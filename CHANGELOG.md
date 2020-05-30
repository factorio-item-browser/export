# Changelog

## Unreleased

### Fixed

- Downloader ignoring the Factorio version and downloading the wrong releases.

### Changed

- Dependency `factorio-item-browser/export-queue-client` to version 1.2.
- Using ordering `priority` to fetch the next job to process.

### Removed

- Support for PHP 7.3. The project must now run with at least PHP 7.4.

## 2.0.7 - 2020-05-23

### Fixed

- Failing to correctly detect the directory within the mod files for certain mods.

## 2.0.6 - 2020-05-15

### Fixed

- Process launching Factorio running in a timeout of 60secs.
- Small mapping issue with icons of shortcuts.

## 2.0.5 - 2020-05-11

### Fixed

- Extracting downloaded Factorio running into a command timeout on slower machines.
- Command `download-factorio` erased potential symlink of `data/factorio`.

## 2.0.4 - 2020-05-03

### Fixed

- Rendering of thumbnails which do not have a size of 144px.
- Wrong error message when mods are not compatible to Factorio (and thus the Dump mod was not loaded at all).

## 2.0.3 - 2020-04-24

### Fixed

- Mismatched version between the mod directory and its generated info.json file.

## 2.0.2 - 2020-04-24

### Fixed

- Missing version in auto-wiring.

## 2.0.1 - 2020-04-24

### Changed

- Extracted icon renderer to Go-based binary for major performance improvement.
- Improved error message in the case that Factorio crashed.

### Fixed

- Export did not recognize tints when specified without named keys.

## 2.0.0 - 2020-04-15

- Full re-implementation of the export project.

### Changed

- ExportData library to latest version.
- Rendering of icons from plain GD to Imagine (using GD).

## Fixed

- Game-internal change from "player" to "character".

## 1.1.0 - 2018-07-21

### Added

- Export of crafting category for recipes.
- Export of machine data.

### Fixed

- Icon tint color possibly using a range of 0-255 instead of 0-1.
- Using not the original size of the icons.

## 1.0.0 - 2018-05-13

- Initial release of the export.
