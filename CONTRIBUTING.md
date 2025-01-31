# Contributing

Thank you for considering contributing to Tenantify! We welcome contributions from the community to help make this package better.

## Development Setup

1. Fork the repository
2. Clone your fork:
```bash
git clone https://github.com/your-username/tenantify.git
cd tenantify
```
3. Install dependencies:
```bash
composer install
```
4. Create a branch for your changes:
```bash
git checkout -b feature/your-feature-name
```

## Development Workflow

1. Make your changes
2. Run the test suite:
```bash
composer test
```
3. Run static analysis:
```bash
composer phpstan
```
4. Run code style checks:
```bash
composer pint
```
5. Ensure all tests pass and there are no style issues

## Pull Request Guidelines

1. **Branch Naming**
   - Feature: `feature/your-feature-name`
   - Bug fix: `fix/issue-description`
   - Documentation: `docs/what-you-changed`

2. **Commit Messages**
   - Use clear, descriptive commit messages
   - Reference any relevant issues
   - Follow conventional commits format when possible

3. **Documentation**
   - Add PHPDoc blocks for new classes and methods
   - Update the README.md if necessary
   - Add examples for new features

4. **Testing**
   - Add tests for new features
   - Ensure all tests pass
   - Add test cases for bug fixes

5. **Code Style**
   - Follow PSR-12 coding standards
   - Use type hints where possible
   - Keep methods focused and concise

## Creating Issues

- Check if the issue already exists
- Use the issue template if provided
- Provide clear steps to reproduce bugs
- Include relevant code samples
- Specify your environment (PHP version, Laravel version, etc.)

## Security Vulnerabilities

If you discover a security vulnerability, please follow our [Security Policy](SECURITY.md). Do NOT create a public issue for security vulnerabilities.

## Code of Conduct

Violations of the code of conduct should be reported to Rasel Islam Rafi at (rtraselbd@gmail.com):

* Participants should embrace tolerance toward differing opinions.

* It is imperative for participants to use language and exhibit behaviors that avoid personal attacks and demeaning comments.

* When evaluating the statements and actions of others, it is crucial to presume positive intentions.

* Any behavior deemed as reasonably constituting harassment is strictly prohibited.

## License

By contributing to Tenantify, you agree that your contributions will be licensed under its MIT license.