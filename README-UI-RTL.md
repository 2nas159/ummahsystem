# UI Patterns and RTL Usage

This app is RTL-first. Bootstrap 5 is used with `dashboard.rtl.css`; custom theme tokens and components live in `css/theme.css` and `css/components.css`.

## Load Order
- Bootstrap CSS → `css/bootstrap.min.css`
- Bootstrap RTL → `css/dashboard.rtl.css`
- Theme tokens → `css/theme.css`
- Components → `css/components.css`

## Tokens
See `css/theme.css` for CSS variables: colors, radii, shadows, borders, text. Use variables instead of hard-coded values.

## Components
Use helpers from `css/components.css`:
- `.kpi-card`, `.card-elevated`
- `.table-modern` with `.table-striped`
- `.filter-chip`
- `.empty-state`

## Accessibility
- Keep `<html dir="rtl" lang="ar">`
- Use semantic landmarks (`header`, `nav`, `main`, `footer`)
- Focus outlines are provided globally

## Scripts
- Defer non-critical scripts
- Header features (search/notifications) in `js/enhanced-header.js`

## APIs
- `api/global_search.php`: supports `q`, `page`, `per_page`
- `api/get_statistics.php`: supports `detail=full|summary`, `recent_limit`
