# Contributing to MyProperty

Thank you for your interest in contributing to MyProperty! This document provides guidelines and information for contributors to help ensure a smooth and effective contribution process.

## 🎯 Our Contribution Philosophy

We welcome contributions from everyone, whether you're fixing a bug, adding a new feature, improving documentation, or helping with community support. We believe that diverse perspectives and experiences make our project stronger.

## 🚀 Getting Started

### Prerequisites

Before you start contributing, please:

1. **Read the Documentation**: Familiarize yourself with the project by reading:
   - [README.md](README.md)
   - [FRONTEND_SETUP.md](docs/FRONTEND_SETUP.md)
   - [BACKEND_SETUP.md](docs/BACKEND_SETUP.md)
   - [API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md)
   - [ARCHITECTURE.md](docs/ARCHITECTURE.md)

2. **Set Up Your Development Environment**: Follow the setup guides to get the project running locally.

3. **Understand the Project Structure**: Review the architecture and code organization.

4. **Check Existing Issues**: Look for existing issues or pull requests related to your intended contribution.

## 📋 Types of Contributions

We welcome the following types of contributions:

### 🐛 Bug Fixes
- Fixing bugs in the frontend or backend
- Improving error handling
- Fixing performance issues
- Security vulnerability fixes

### ✨ New Features
- Adding new functionality to the frontend or backend
- Enhancing existing features
- Adding new AI capabilities
- Improving user experience

### 📚 Documentation
- Improving existing documentation
- Adding new documentation
- Translating documentation
- Creating tutorials and guides

### 🧪 Testing
- Adding unit tests
- Improving test coverage
- Adding integration tests
- Performance testing

### 🎨 Code Quality
- Refactoring code for better maintainability
- Improving code organization
- Adding type definitions
- Optimizing performance

## 🔄 Development Workflow

### 1. Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone https://github.com/your-username/myproperty.git
   cd myproperty
   ```

### 2. Create a Branch

Create a new branch for your contribution:
```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/your-bug-fix
```

### 3. Make Your Changes

- Follow the coding standards outlined below
- Make small, focused changes
- Add tests for new functionality
- Update documentation as needed

### 4. Test Your Changes

- Run the test suite:
  ```bash
  # Backend tests
  cd backend && php artisan test
  
  # Frontend tests
  cd frontend && npm test
  ```
- Test your changes manually
- Ensure the application builds successfully

### 5. Commit Your Changes

Follow our commit message guidelines:
```bash
git add .
git commit -m "feat: add new feature description"
```

### 6. Push and Create a Pull Request

Push your branch and create a pull request:
```bash
git push origin feature/your-feature-name
```

## 📝 Coding Standards

### General Guidelines

- **Write Clean Code**: Follow SOLID principles
- **Be Consistent**: Follow existing code style
- **Add Comments**: Explain complex logic
- **Keep It Simple**: Avoid over-engineering

### Backend (Laravel)

#### PHP Standards
- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use Laravel's coding style
- Add type hints where appropriate
- Use Eloquent relationships efficiently

#### Code Organization
```php
// Controller example
class PropertyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $properties = Property::with(['agent', 'location'])
            ->filter($request->all())
            ->paginate(15);

        return response()->json($properties);
    }
}
```

#### Database
- Use migrations for schema changes
- Add proper indexes for performance
- Use factories for testing
- Follow naming conventions

### Frontend (React + TypeScript)

#### TypeScript Standards
- Use strict TypeScript
- Define proper interfaces and types
- Avoid `any` type when possible
- Use proper typing for API responses

#### Component Structure
```typescript
// Component example
interface PropertyCardProps {
  property: Property;
  onEnquiry?: (propertyId: number) => void;
}

const PropertyCard: React.FC<PropertyCardProps> = ({ 
  property, 
  onEnquiry 
}) => {
  return (
    <Card className="property-card">
      {/* Component content */}
    </Card>
  );
};
```

#### State Management
- Use React Context for global state
- Use TanStack Query for server state
- Keep local state minimal
- Use proper loading and error states

## 🧪 Testing Guidelines

### Backend Testing

#### Unit Tests
- Test business logic in isolation
- Mock external dependencies
- Test edge cases and error conditions
- Aim for high code coverage

#### Feature Tests
- Test API endpoints
- Test user workflows
- Test authentication and authorization
- Test database interactions

### Frontend Testing

#### Component Tests
- Test component rendering
- Test user interactions
- Test state changes
- Test error boundaries

#### Integration Tests
- Test API integration
- Test user flows
- Test navigation
- Test form submissions

## 📋 Pull Request Guidelines

### PR Description

Your pull request should include:

1. **Clear Title**: Use conventional commit format
2. **Detailed Description**: Explain what you changed and why
3. **Testing Information**: How you tested your changes
4. **Screenshots**: For UI changes
5. **Breaking Changes**: If applicable

### PR Checklist

Before submitting a PR, ensure:

- [ ] Code follows project standards
- [ ] Tests pass locally
- [ ] Documentation is updated
- [ ] No breaking changes (or clearly documented)
- [ ] Security considerations addressed
- [ ] Performance implications considered

### Review Process

1. **Self-Review**: Review your own changes first
2. **Automated Checks**: CI/CD pipeline validation
3. **Peer Review**: Community review and feedback
4. **Approval**: Maintainer approval

## 🏷 Branching Strategy

### Main Branches

- **`main`**: Production-ready code
- **develop**: Integration branch for features

### Feature Branches

- **`feature/feature-name`**: New features
- **`fix/bug-description`**: Bug fixes
- **hotfix/critical-fix`**: Critical fixes
- **docs/documentation-update`**: Documentation changes

### Branch Naming

- Use kebab-case for branch names
- Be descriptive and concise
- Include issue number when applicable: `feature/123-add-user-profile`

## 📖 Commit Message Guidelines

Follow [Conventional Commits](https://www.conventionalcommits.org/) specification:

### Format
```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Types

- **`feat`**: New features
- **`fix`**: Bug fixes
- **docs`**: Documentation changes
- **style`**: Code style changes (formatting, missing semicolons)
- **refactor`**: Code refactoring
- **test`**: Adding or updating tests
- **chore`: Maintenance tasks
- **perf`**: Performance improvements
- **ci`: CI/CD changes

### Examples

```bash
feat: add user profile management
fix: resolve authentication token expiration
docs: update API documentation
style: fix code formatting issues
refactor: simplify property service logic
test: add unit tests for price calculation
chore: update dependencies
```

## 🤝 Community Guidelines

### Code Review

- **Be Constructive**: Provide helpful, specific feedback
- **Be Respectful**: Treat all contributors with respect
- **Be Thorough**: Review code carefully and thoughtfully
- **Be Responsive**: Respond to review comments promptly

### Communication

- **Be Clear**: Use clear and concise language
- **Be Patient**: Allow time for review and discussion
- **Be Helpful**: Offer assistance when you can
- **Be Professional**: Maintain professional communication

### Conflict Resolution

- **Discuss First**: Try to resolve conflicts through discussion
- **Escalate When Needed**: Ask maintainers for help if needed
- **Focus on the Code**: Keep discussions focused on technical merits
- **Be Willing to Compromise**: Find solutions that work for everyone

## 🏆 Recognition

### Contributors

All contributors are recognized in various ways:

- **GitHub Contributors List**: Automatic recognition
- **Release Notes**: Mentioned in release announcements
- **Community Highlights**: Featured in community communications
- **Special Recognition**: For outstanding contributions

### Recognition Criteria

- **Quality**: High-quality, well-tested code
- **Impact**: Significant improvements to the project
- **Consistency**: Regular and reliable contributions
- **Community**: Helpful and supportive community participation

## 📞 Getting Help

If you need help with your contribution:

1. **Check Documentation**: Review existing documentation first
2. **Search Issues**: Look for similar issues or discussions
3. **Ask Questions**: Create an issue with the `question` label
4. **Join Discussions**: Participate in GitHub discussions
5. **Contact Maintainers**: Reach out to project maintainers

## 🎯 Success Metrics

We consider contributions successful when they:

- **Improve the Project**: Make meaningful improvements
- **Maintain Quality**: Follow coding standards and best practices
- **Enhance Documentation**: Keep documentation up-to-date
- **Support Community**: Help other contributors
- **Follow Guidelines**: Adhere to contribution guidelines

## 📚 Resources

### Documentation
- [Project README](README.md)
- [Frontend Setup Guide](docs/FRONTEND_SETUP.md)
- [Backend Setup Guide](docs/BACKEND_SETUP.md)
- [API Documentation](docs/API_DOCUMENTATION.md)
- [Architecture Overview](docs/ARCHITECTURE.md)
- [AI Features Guide](docs/AI_FEATURES.md)

### Development Tools
- [Laravel Documentation](https://laravel.com/docs)
- [React Documentation](https://react.dev/)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)
- [Tailwind CSS](https://tailwindcss.com/docs)

### Community
- [GitHub Discussions](https://github.com/your-username/myproperty/discussions)
- [GitHub Issues](https://github.com/your-username/myproperty/issues)
- [Discord Server](https://discord.gg/your-invite) (if available)

---

## 🎉 Thank You

Thank you for considering contributing to MyProperty! Your contributions help make this project better for everyone. We appreciate your time, effort, and expertise.

Whether you're fixing a bug, adding a new feature, improving documentation, or helping others, your contributions make a real difference.

Happy coding! 🚀
