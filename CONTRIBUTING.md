# Contributing

Thank you for your interest in contributing to the **Laundry Management System**!
We welcome contributions from our team and the community. Please follow these guidelines to ensure smooth collaboration.

## Development workflow

1. **Fork the repository**
   - Go to [nncast/laravel-laundry-management-system](https://github.com/nncast/laravel-laundry-management-system).
   - Click the **Fork** button in the top-right corner to create a copy under your GitHub account.
   - Clone your fork locally:
     ```bash
     git clone https://github.com/<your-username>/laravel-laundry-management-system.git
     cd laravel-laundry-management-system
     ```
   - Add the original repository as an upstream remote so you can sync changes:
     ```bash
     git remote add upstream https://github.com/nncast/laravel-laundry-management-system.git
     ```

2. **Create a branch** from `main` in your fork:
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes**, then commit and push to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

4. **Open a pull request** against `nncast/laravel-laundry-management-system:main`.

## Before you submit

- Keep commit messages clear and descriptive.
- Avoid committing secrets, credentials, your `.env` file, `vendor/`, database files, or backups from `storage/app/backups`.
- If you add or change behavior, update relevant documentation.
- If you change the database schema, add a new migration — don't edit one that has already been released.
- Run the test suite before opening a pull request (see [`README.md`](README.md#running-tests)):
  ```bash
  php artisan test
  ```

## Code style

- Follow the existing project conventions.
- Prefer small, reviewable changes.
- Do not add unrelated formatting changes.

## Pull requests

Pull requests should include:

- a short summary of the change
- any relevant context or motivation
- testing steps or validation performed

## Security

Do not commit sensitive values such as database credentials, `APP_KEY`, passwords, tokens, or private configuration.

For security reports, follow [SECURITY.md](https://github.com/nncast/laravel-laundry-management-system/blob/main/SECURITY.md).
