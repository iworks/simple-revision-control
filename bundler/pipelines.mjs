/**
 * Asset pipelines — CSS (sass → minify) and JS (concat → terser).
 * Output paths and file names mirror the old Grunt pipeline exactly
 * (the PHP code enqueues these files — never change them).
 */

import { dirname, join, resolve } from "node:path";
import { log } from "./consoleHelpers.mjs";
import { config } from "./config.mjs";
import {
  compileSCSSEntries,
  concatFiles,
  minifyCSS,
  minifyJS,
  writeFileAtomic,
  prettierSources,
  cleanGlob,
} from "./tools.mjs";

const root = resolve(dirname(new URL(import.meta.url).pathname), "..");

/**
 * Banner prepended to concatenated bundles (grunt `concat` template).
 *
 * @return {string}
 */
function concatBanner() {
  return [
    `/*! ${config.title} - ${config.version}`,
    ` * ${config.homepage}`,
    ` * Copyright (c) ${config.buildYear}`,
    ` * Licensed ${config.license} */`,
    "",
  ].join("\n");
}

/**
 * Banner prepended to minified bundles (grunt `uglify` template — note the `;`).
 *
 * @return {string}
 */
function minBanner() {
  return [
    `/*! ${config.title} - ${config.version}`,
    ` * ${config.homepage}`,
    ` * Copyright (c) ${config.buildYear};`,
    ` * Licensed ${config.license} */`,
    "",
  ].join("\n");
}

/**
 * Strip sourceMappingURL comments (build mode — no maps are written).
 *
 * @param {string} css CSS content.
 * @return {string}
 */
function stripMapComments(css) {
  return css.replace(/\/\*# sourceMappingURL=[^*]*\*\/\n?/g, "");
}

/**
 * CSS pipeline: compile SCSS entries → minify.
 * (The old Grunt pipeline had sass + cssmin; this plugin currently has no
 * SCSS sources, so this is a no-op until entries appear in assets/sass.)
 *
 * @param {object} options
 * @param {boolean} options.maps   Write source maps (dev mode).
 * @param {boolean} options.strip  Strip sourceMappingURL comments.
 */
export async function buildCSS({ maps = false, strip = false } = {}) {
  const compiled = await compileSCSSEntries(maps);
  for (const outPath of compiled) {
    if (strip) {
      let css = await Bun.file(outPath).text();
      css = stripMapComments(css);
      writeFileAtomic(outPath, css);
    }
    const css = await Bun.file(outPath).text();
    const min = minifyCSS(css);
    writeFileAtomic(outPath.replace(/\.css$/, ".min.css"), minBanner() + min);
  }
  log(`css — ${compiled.length} SCSS entries processed`, "s");
}

/**
 * JS pipeline: concat admin sources → admin.js → terser minify → admin.min.js.
 */
export async function buildJS() {
  const srcDir = join(root, config.jsSrcDir);
  const files = [];
  for (const file of config.jsFiles) {
    const path = join(srcDir, file);
    if (await Bun.file(path).exists()) {
      files.push(path);
    }
  }
  if (!files.length) {
    log(`js — ${config.jsSrcDir}/ has no sources, bundle skipped`, "w");
    return;
  }
  const concat = await concatFiles(files, concatBanner());
  const outFile = join(root, config.scriptsDir, config.jsBundle);
  writeFileAtomic(outFile, concat);
  const min = await minifyJS(concat, minBanner());
  writeFileAtomic(outFile.replace(/\.js$/, ".min.js"), min);
  log("js — bundles concatenated and minified", "s");
}

/**
 * Prettier pass over the JS/JSON sources (build pipelines run it first).
 */
export async function formatSources() {
  await prettierSources([
    "assets/scripts/src/**/*.js",
    "bundler/**/*.js",
    "bundler/**/*.mjs",
    "*.js",
  ]);
  log("prettier — sources formatted", "s");
}

/**
 * Remove built files (grunt `clean` semantics).
 */
export async function cleanCompiled() {
  await cleanGlob("assets/scripts/*.js");
  await cleanGlob("assets/scripts/*.map");
  await cleanGlob("assets/styles/**/*.css");
  await cleanGlob("assets/styles/**/*.map");
  await cleanGlob("languages/*.mo");
  log("clean — built JS/CSS and languages/*.mo removed", "i");
}
