# Frontend Setup Guide

This guide will help you set up the React + TypeScript frontend for the MyProperty Real Estate Management System.

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **Node.js**: Version 20.0.0 or higher
- **npm**: Version 9.0.0 or higher (comes with Node.js)
- **Git**: For version control
- **Backend API**: The Laravel backend must be running (see [Backend Setup Guide](BACKEND_SETUP.md))

## 🚀 Quick Setup (5 minutes)

If you want to get started quickly, follow these steps:

```bash
# Navigate to frontend directory
cd frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Start development server
npm run dev
```

That's it! The frontend will be available at `http://localhost:5173`.

## 📖 Detailed Setup

### 1. Clone and Navigate

```bash
# If you haven't cloned the repository yet
git clone https://github.com/your-username/myproperty.git
cd myproperty/frontend

# If you're already in the repository
cd frontend
```

### 2. Install Dependencies

```bash
npm install
```

This will install all the required packages including:
- React 19 + TypeScript
- Vite (build tool)
- Tailwind CSS
- React Router
- TanStack Query
- Axios
- And all other dependencies

### 3. Environment Configuration

Copy the environment template and configure it:

```bash
cp .env.example .env
```

Edit the `.env` file with your configuration:

```env
# API Configuration
VITE_API_URL=http://localhost:8000/api/v1

# Environment
VITE_NODE_ENV=development

# Application Settings
VITE_APP_NAME=MyProperty
VITE_APP_DESCRIPTION=Real Estate Property Management System

# Feature Flags
VITE_ENABLE_AI_FEATURES=true
VITE_ENABLE_ANALYTICS=true
VITE_ENABLE_DARK_MODE=true

# External Services (Optional)
# VITE_GOOGLE_MAPS_API_KEY=your_google_maps_api_key
# VITE_STRIPE_PUBLISHABLE_KEY=your_stripe_publishable_key

# Debug Settings
VITE_DEBUG_MODE=true
```

#### Environment Variables Explained

| Variable | Description | Default |
|----------|-------------|---------|
| `VITE_API_URL` | Backend API endpoint URL | `http://localhost:8000/api/v1` |
| `VITE_APP_NAME` | Application name | `MyProperty` |
| `VITE_ENABLE_AI_FEATURES` | Enable AI-powered features | `true` |
| `VITE_ENABLE_DARK_MODE` | Enable dark mode toggle | `true` |
| `VITE_GOOGLE_MAPS_API_KEY` | Google Maps API key (optional) | - |

### 4. Start Development Server

```bash
npm run dev
```

The development server will start at `http://localhost:5173`. Vite provides hot module replacement, so changes will be reflected immediately.

## 🛠️ Available Scripts

Here are all the available npm scripts:

```bash
# Start development server with hot reload
npm run dev

# Build for production
npm run build

# Preview production build locally
npm run preview

# Run ESLint
npm run lint

# Run TypeScript type checking
npm run type-check

# Run tests (when implemented)
npm run test
```

## 🏗️ Project Structure

Understanding the frontend structure:

```
frontend/
├── public/                 # Static assets
│   ├── favicon.ico
│   └── index.html
├── src/
│   ├── components/          # Reusable components
│   │   ├── auth/           # Authentication components
│   │   ├── forms/          # Form components
│   │   ├── layout/         # Layout components
│   │   └── ui/             # UI components (buttons, inputs, etc.)
│   ├── contexts/            # React contexts
│   │   ├── AuthContext.tsx
│   │   └── ThemeContext.tsx
│   ├── lib/                # Utilities and services
│   │   └── api.ts          # API service layer
│   ├── pages/              # Page components
│   │   ├── auth/           # Authentication pages
│   │   ├── dashboard/      # Dashboard pages
│   │   ├── public/         # Public pages
│   │   ├── user/           # User pages
│   │   ├── agent/          # Agent pages
│   │   └── admin/          # Admin pages
│   ├── layouts/            # Layout components
│   │   ├── PublicLayout.tsx
│   │   ├── AuthLayout.tsx
│   │   └── DashboardLayout.tsx
│   ├── types/              # TypeScript type definitions
│   │   └── index.ts
│   ├── App.tsx             # Main App component
│   ├── main.tsx            # Application entry point
│   └── index.css           # Global styles
├── .env.example            # Environment variables template
├── package.json            # Dependencies and scripts
├── tailwind.config.js      # Tailwind CSS configuration
├── tsconfig.json           # TypeScript configuration
└── vite.config.ts          # Vite configuration
```

## 🎨 Styling and Theming

The frontend uses Tailwind CSS with a custom design system:

### Color System
- **Primary**: Blue palette for main actions
- **Secondary**: Gray palette for secondary elements
- **Success**: Green for success states
- **Warning**: Yellow for warnings
- **Error**: Red for errors

### Dark Mode
The application supports dark mode with three options:
- **Light**: Always light theme
- **Dark**: Always dark theme
- **System**: Follows system preference (default)

### Custom Components
All UI components are built with Tailwind CSS classes and follow a consistent design system.

## 🔧 Development Workflow

### 1. Making Changes
- Edit components in `src/components/`
- Edit pages in `src/pages/`
- Styles are in `src/index.css` and component files
- Types are defined in `src/types/`

### 2. Adding New Components
1. Create component in appropriate directory
2. Export from component file
3. Import where needed
4. Follow TypeScript best practices

### 3. API Integration
- Use the `apiService` from `src/lib/api.ts`
- All API calls go through TanStack Query
- Handle loading and error states properly

## 🐛 Common Issues and Solutions

### Issue: "Module not found" errors
**Solution**: Make sure you're in the `frontend` directory and run `npm install`

### Issue: API connection errors
**Solution**: 
1. Ensure the backend is running on `http://localhost:8000`
2. Check `VITE_API_URL` in your `.env` file
3. Verify CORS settings in the backend

### Issue: TypeScript errors
**Solution**: Run `npm run type-check` to identify and fix type issues

### Issue: Tailwind CSS not working
**Solution**: Ensure you're importing `src/index.css` in `main.tsx`

### Issue: Hot reload not working
**Solution**: Restart the dev server with `npm run dev`

## 🧪 Testing

While comprehensive tests are planned, you can currently run:

```bash
# Type checking
npm run type-check

# Linting
npm run lint
```

## 📦 Build for Production

When you're ready to deploy:

```bash
# Build the application
npm run build

# Preview the build locally
npm run preview
```

The build files will be in the `dist/` directory.

## 🔍 Debugging

### Enable Debug Mode
Set `VITE_DEBUG_MODE=true` in your `.env` file to enable additional logging.

### Browser DevTools
- Use React DevTools for component inspection
- Use browser network tab to debug API calls
- Console logs will show detailed information in debug mode

## 🚀 Deployment

### Environment Variables for Production
Create a `.env.production` file with production-specific values:

```env
VITE_API_URL=https://your-domain.com/api/v1
VITE_NODE_ENV=production
VITE_DEBUG_MODE=false
```

### Build Process
```bash
npm run build
```

Upload the `dist/` folder to your web server or hosting provider.

## 📚 Additional Resources

- [React Documentation](https://react.dev/)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Vite Documentation](https://vitejs.dev/)
- [TanStack Query Documentation](https://tanstack.com/query/latest)

## 🆘 Getting Help

If you encounter issues:

1. Check this guide for solutions
2. Search existing [GitHub Issues](https://github.com/your-username/myproperty/issues)
3. Create a new issue with detailed information
4. Join our [Discord](https://discord.gg/your-invite) (if available)

---

Happy coding! 🎉
