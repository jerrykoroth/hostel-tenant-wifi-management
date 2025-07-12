# Contributing to Tenant Management System

Thank you for your interest in contributing to the Tenant Management System! 🎉

## 🚀 Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/yourusername/tenant-management-app.git
   cd tenant-management-app
   ```
3. **Set up the development environment** by following the [INSTALLATION.md](INSTALLATION.md) guide

## 🔄 Development Workflow

1. **Create a new branch** for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes** following the coding standards below

3. **Test your changes** thoroughly:
   - Test on different browsers
   - Test mobile responsiveness
   - Verify database operations work correctly

4. **Commit your changes**:
   ```bash
   git add .
   git commit -m "Add: Description of your changes"
   ```

5. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create a Pull Request** on GitHub

## 📝 Coding Standards

### PHP
- Use **PSR-12** coding standard
- Always use **prepared statements** for database queries
- **Sanitize all user inputs** using the provided `sanitize_input()` function
- Add **meaningful comments** for complex logic
- Follow **secure coding practices**

### JavaScript
- Use **ES6+** features where appropriate
- Keep functions **small and focused**
- Use **meaningful variable names**
- Add **error handling** for API calls

### CSS
- Use **Bootstrap classes** where possible
- Keep **custom CSS minimal**
- Ensure **mobile responsiveness**
- Use **CSS custom properties** for theme colors

### Database
- Always use **foreign key constraints**
- Add **appropriate indexes** for performance
- Include **migration scripts** for schema changes
- Document any **breaking changes**

## 🐛 Bug Reports

When reporting bugs, please include:
- **PHP version** and server environment
- **Steps to reproduce** the issue
- **Expected vs actual behavior**
- **Screenshots** if applicable
- **Browser and device** information

## ✨ Feature Requests

For new features:
- **Search existing issues** first
- **Describe the use case** clearly
- **Explain the expected behavior**
- **Consider the impact** on existing users

## 🔒 Security

- **Never commit** sensitive information (passwords, API keys, etc.)
- **Follow security best practices** (input validation, output encoding, etc.)
- **Report security vulnerabilities** privately to jerrykoroth@gmail.com

## 📋 Pull Request Guidelines

### Before Submitting
- [ ] Code follows the project's coding standards
- [ ] All tests pass (if applicable)
- [ ] Documentation is updated (if needed)
- [ ] No sensitive information is committed
- [ ] Branch is up to date with main

### PR Description Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tested on desktop browsers
- [ ] Tested on mobile devices
- [ ] Database operations verified
- [ ] No errors in browser console

## Screenshots (if applicable)
Add screenshots of the changes
```

## 🏗️ Project Structure

```
tenant-management/
├── index.php              # Landing page
├── mobile_app.php         # Main mobile application
├── room_management.php    # Room & bed management
├── setup.php             # Installation wizard
├── database/             # Database schema and migrations
├── includes/             # Configuration and utility files
├── uploads/              # File upload directory
├── docs/                 # Documentation files
└── assets/               # Static assets (if any)
```

## 🎯 Priority Areas for Contribution

1. **🔒 Security enhancements**
2. **📱 Mobile UX improvements**
3. **📊 Advanced reporting features**
4. **🌐 Internationalization (i18n)**
5. **⚡ Performance optimizations**
6. **🧪 Test coverage**
7. **📚 Documentation improvements**

## 💬 Questions?

- **Email**: jerrykoroth@gmail.com
- **GitHub Issues**: For public discussions
- **Documentation**: Check README.md and INSTALLATION.md

## 📄 License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).

---

**Thank you for making the Tenant Management System better! 🙏**
