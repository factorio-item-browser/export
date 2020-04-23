# Changelog

## Unreleased

### Changed

- Extracted icon renderer to Go-based binary for major performance improvement.

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
