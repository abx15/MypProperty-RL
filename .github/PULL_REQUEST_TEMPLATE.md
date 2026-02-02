## 📋 Pull Request Overview

### 🎯 Type of Change
- [ ] 🐛 Bug fix (non-breaking change which fixes an issue)
- [ ] ✨ New feature (non-breaking change which adds functionality)
- [ ] 💥 Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] 📚 Documentation update
- [ ] 🎨 Code style / refactoring
- [ ] 🧪 Test improvement
- [ ] 🔧 Configuration change
- [ ] 🚀 Performance improvement
- [ ] 🔒 Security fix

### 📝 Description
Please include a summary of the change and which issue is fixed. Please also include relevant motivation and context. List any dependencies that are required for this change.

## 🔗 Related Issues

Fixes #(issue number)
Closes #(issue number)

## 🧪 Testing

### 📋 Test Plan
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] E2E tests pass (if applicable)
- [ ] Manual testing completed

### 🧪 Test Cases
Please describe the tests that you ran to verify your changes:

1. **Test Case 1**: [Description]
   - [ ] Expected result
   - [ ] Actual result

2. **Test Case 2**: [Description]
   - [ ] Expected result
   - [ ] Actual result

3. **Test Case 3**: [Description]
   - [ ] Expected result
   - [ ] Actual result

## 📸 Screenshots / Videos

If applicable, add screenshots or videos to help verify your changes.

### Before Changes
*(Add screenshots or videos here)*

### After Changes
*(Add screenshots or videos here)*

## 🔧 Technical Details

### 📂 Files Changed
List the files that were changed:

```bash
# Backend
backend/app/Http/Controllers/ExampleController.php
backend/database/migrations/2024_01_01_000000_create_example_table.php
backend/tests/Feature/ExampleTest.php

# Frontend
frontend/src/components/ExampleComponent.tsx
frontend/src/pages/ExamplePage.tsx
frontend/src/types/index.ts
```

### 🔄 Database Changes
If applicable, describe the database changes:

```sql
-- Migration example
CREATE TABLE examples (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 🎨 Frontend Changes
If applicable, describe the frontend changes:

- **New Components**: [Component names]
- **Updated Components**: [Component names]
- **New Pages**: [Page names]
- **API Changes**: [API endpoints]

### 🔌 API Changes
If applicable, describe the API changes:

- **New Endpoints**: [Endpoint descriptions]
- **Modified Endpoints**: [Endpoint descriptions]
- **Breaking Changes**: [Breaking change descriptions]

## 📋 Checklist

### 🧪 Code Quality
- [ ] My code follows the project's coding standards
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings

### 🧪 Testing
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes
- [ ] Any dependent changes have been merged and published in downstream modules

### 📚 Documentation
- [ ] I have updated the documentation accordingly
- [ ] I have added necessary README entries
- [ ] I have updated the API documentation (if applicable)

### 🔒 Security
- [ ] I have considered the security implications of my changes
- [ ] I have followed the security best practices
- [ ] I have tested for common vulnerabilities

### 🚀 Performance
- [ ] I have considered the performance implications of my changes
- [ ] I have tested for performance regressions
- [ ] I have optimized database queries (if applicable)

## 📊 Performance Impact

### 📈 Metrics
If applicable, describe the performance impact:

- **Before**: [Performance metrics before changes]
- **After**: [Performance metrics after changes]
- **Improvement**: [Performance improvement percentage]

### 🗄️ Database Performance
If applicable, describe the database performance impact:

- **Query Optimization**: [Optimizations made]
- **Index Changes**: [Indexes added/removed]
- **Migration Time**: [Migration execution time]

## 🔍 Review Process

### 👀 Code Review
Please tag the reviewers who should review this PR:

- @reviewer1
- @reviewer2

### 📋 Review Checklist
Please review this PR against the following checklist:

#### 🐛 Bug Fixes
- [ ] The bug is actually fixed
- [ ] The fix doesn't introduce new bugs
- [ ] The fix handles edge cases properly
- [ ] The fix is properly tested

#### ✨ New Features
- [ ] The feature works as expected
- [ ] The feature is well-documented
- [ ] The feature is properly tested
- [ ] The feature follows the project's design patterns

#### 💥 Breaking Changes
- [ ] The breaking change is necessary
- [ ] The breaking change is well-documented
- [ ] Migration path is provided
- [ ] The breaking change is properly communicated

## 📝 Additional Notes

Add any other context about the pull request here.

## 🤝 Contributing

By submitting this pull request, you agree to:

- Follow the project's code of conduct
- Be responsive to feedback and review comments
- Help maintain the quality of the project
- Contribute to the project's documentation

## 🎉 Thank You

Thank you for contributing to the MyProperty project! Your contributions help make this project better for everyone.

---

## 📞 Contact

If you have any questions about this pull request, please:

- Leave a comment below
- Contact the maintainers
- Join our [Discord](https://discord.gg/your-invite) (if available)

---

**Happy coding! 🎉**
