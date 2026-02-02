import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';
import { AuthProvider } from './contexts/AuthContext';
import { ThemeProvider } from './contexts/ThemeContext';
import { ToastProvider } from './components/ui/Toaster';

// Layouts
import { PublicLayout } from './layouts/PublicLayout';
import { AuthLayout } from './layouts/AuthLayout';
import { DashboardLayout } from './layouts/DashboardLayout';

// Public Pages
import { HomePage } from './pages/public/HomePage';
import { PropertiesPage } from './pages/public/PropertiesPage';
import { PropertyDetailPage } from './pages/public/PropertyDetailPage';
import { AboutPage } from './pages/public/AboutPage';
import { ContactPage } from './pages/public/ContactPage';

// Auth Pages
import { LoginPage } from './pages/auth/LoginPage';
import { RegisterPage } from './pages/auth/RegisterPage';
import { ForgotPasswordPage } from './pages/auth/ForgotPasswordPage';

// Dashboard Pages
import { UserDashboard } from './pages/dashboard/UserDashboard';
import { AgentDashboard } from './pages/dashboard/AgentDashboard';
import { AdminDashboard } from './pages/dashboard/AdminDashboard';

// User Pages
import { ProfilePage } from './pages/user/ProfilePage';
import { WishlistPage } from './pages/user/WishlistPage';
import { EnquiriesPage } from './pages/user/EnquiriesPage';

// Agent Pages
import { AgentPropertiesPage } from './pages/agent/AgentPropertiesPage';
import { AgentEnquiriesPage } from './pages/agent/AgentEnquiriesPage';
import { AgentProfilePage } from './pages/agent/AgentProfilePage';

// Admin Pages
import { AdminUsersPage } from './pages/admin/AdminUsersPage';
import { AdminPropertiesPage } from './pages/admin/AdminPropertiesPage';
import { AdminLocationsPage } from './pages/admin/AdminLocationsPage';
import { AdminEnquiriesPage } from './pages/admin/AdminEnquiriesPage';
import { AdminAnalyticsPage } from './pages/admin/AdminAnalyticsPage';
import { AdminAIPage } from './pages/admin/AdminAIPage';

// Components
import { ProtectedRoute } from './components/auth/ProtectedRoute';
import { RoleBasedRoute } from './components/auth/RoleBasedRoute';

// Create a client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <ThemeProvider>
        <AuthProvider>
          <ToastProvider>
            <Router>
              <div className="min-h-screen bg-background text-foreground">
                <Routes>
                  {/* Public Routes */}
                  <Route path="/" element={<PublicLayout />}>
                    <Route index element={<HomePage />} />
                    <Route path="properties" element={<PropertiesPage />} />
                    <Route path="properties/:slug" element={<PropertyDetailPage />} />
                    <Route path="about" element={<AboutPage />} />
                    <Route path="contact" element={<ContactPage />} />
                  </Route>

                  {/* Auth Routes */}
                  <Route path="/auth" element={<AuthLayout />}>
                    <Route path="login" element={<LoginPage />} />
                    <Route path="register" element={<RegisterPage />} />
                    <Route path="forgot-password" element={<ForgotPasswordPage />} />
                  </Route>

                  {/* Protected Dashboard Routes */}
                  <Route
                    path="/dashboard"
                    element={
                      <ProtectedRoute>
                        <DashboardLayout />
                      </ProtectedRoute>
                    }
                  >
                    {/* User Dashboard */}
                    <Route
                      index
                      element={
                        <RoleBasedRoute roles={['user']}>
                          <UserDashboard />
                        </RoleBasedRoute>
                      }
                    />

                    {/* Agent Dashboard */}
                    <Route
                      path="agent"
                      element={
                        <RoleBasedRoute roles={['agent']}>
                          <AgentDashboard />
                        </RoleBasedRoute>
                      }
                    />

                    {/* Admin Dashboard */}
                    <Route
                      path="admin"
                      element={
                        <RoleBasedRoute roles={['admin']}>
                          <AdminDashboard />
                        </RoleBasedRoute>
                      }
                    />
                  </Route>

                  {/* User Routes */}
                  <Route
                    path="/user"
                    element={
                      <ProtectedRoute>
                        <DashboardLayout />
                      </ProtectedRoute>
                    }
                  >
                    <Route path="profile" element={<ProfilePage />} />
                    <Route path="wishlist" element={<WishlistPage />} />
                    <Route path="enquiries" element={<EnquiriesPage />} />
                  </Route>

                  {/* Agent Routes */}
                  <Route
                    path="/agent"
                    element={
                      <ProtectedRoute>
                        <DashboardLayout />
                      </ProtectedRoute>
                    }
                  >
                    <Route path="properties" element={<AgentPropertiesPage />} />
                    <Route path="enquiries" element={<AgentEnquiriesPage />} />
                    <Route path="profile" element={<AgentProfilePage />} />
                  </Route>

                  {/* Admin Routes */}
                  <Route
                    path="/admin"
                    element={
                      <ProtectedRoute>
                        <DashboardLayout />
                      </ProtectedRoute>
                    }
                  >
                    <Route path="users" element={<AdminUsersPage />} />
                    <Route path="properties" element={<AdminPropertiesPage />} />
                    <Route path="locations" element={<AdminLocationsPage />} />
                    <Route path="enquiries" element={<AdminEnquiriesPage />} />
                    <Route path="analytics" element={<AdminAnalyticsPage />} />
                    <Route path="ai" element={<AdminAIPage />} />
                  </Route>

                  {/* Fallback Route */}
                  <Route path="*" element={<Navigate to="/" replace />} />
                </Routes>
              </div>
            </Router>
          </ToastProvider>
        </AuthProvider>
      </ThemeProvider>
      <ReactQueryDevtools initialIsOpen={false} />
    </QueryClientProvider>
  );
}

export default App;
