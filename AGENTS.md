# Repository Guidelines

## Project Structure & Module Organization
- Laravel 10 app with modular architecture (`nwidart/laravel-modules`).
- Core app: `app/`, `routes/`, `config/`, `database/`, `resources/`, `public/`, `storage/`.
- Modules live in `Modules/<Name>/` (e.g., `Http/`, `Entities/`, `Services/`, `Routes/`, `Resources/`). Keep features inside their module.
- Tests: `tests/Feature`, `tests/Unit`, and browser tests in `tests/Browser/**`.

## Build, Test, and Development Commands
- Bootstrap: `composer install`, `npm install`, `cp .env.example .env`, `php artisan key:generate`.
- DB setup: `php artisan migrate --seed`; files: `php artisan storage:link`.
- Run locally: `php artisan serve` (or your web server pointing to `public/`).
- Assets: `npm run dev` (watch: `npm run watch`; production: `npm run prod`).
- Tests: `php artisan test` or `vendor/bin/phpunit`; Dusk: `php artisan dusk` (requires ChromeDriver and valid `APP_URL`).
- Cache/tools: `php artisan optimize:clear`, list modules: `php artisan module:list`.

## Coding Style & Naming Conventions
- PHP follows PSR-12; indent 4 spaces. Blade: 4 spaces. JS/SCSS: 2 spaces.
- Classes/Controllers/Services use PascalCase (e.g., `CustomerService`, `OrderController`); methods/vars use camelCase.
- Blade view filenames use snake/kebab case (e.g., `customers/index.blade.php`).
- Modules are singular PascalCase (e.g., `Modules/Customer`). Avoid cross-module coupling; share via services/contracts.

## Testing Guidelines
- Place unit/feature tests under `tests/Unit|Feature/*Test.php`.
- Dusk specs under `tests/Browser/**`; keep selectors stable and prefer page-objects/components.
- Cover services, rules, and controllers around edge cases. Run `php artisan test` locally before opening a PR.

## Commit & Pull Request Guidelines
- Commits: short, imperative subject (e.g., "Enable SSO login"), optional body with rationale and scope.
- PRs: clear description, linked issues, steps to verify, and screenshots/GIFs for UI changes.
- Keep PRs focused; include migration/seed/doc updates when behavior or schema changes. All tests must pass.

## Security & Configuration Tips
- Never commit secrets. Manage `.env`; ensure `APP_KEY` is set.
- Use per-environment configs; rotate keys on leaks. Validate uploads and user input.

## Agent-Specific Notes
- Prefer minimal, scoped changes; respect module boundaries and existing patterns.
- Update adjacent docs/tests when behavior changes; avoid introducing new dependencies without discussion.

