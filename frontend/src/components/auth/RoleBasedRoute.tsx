import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import type { UserRole } from '../../types';

interface RoleBasedRouteProps {
  children: React.ReactNode;
  roles: UserRole[];
}

export const RoleBasedRoute: React.FC<RoleBasedRouteProps> = ({ children, roles }) => {
  const { user, isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="loading-spinner w-8 h-8 border-2 border-gray-300 border-t-primary"></div>
      </div>
    );
  }

  if (!isAuthenticated || !user) {
    return <Navigate to="/auth/login" replace />;
  }

  if (!roles.includes(user.role.name as UserRole)) {
    // Redirect to appropriate dashboard based on user role
    const roleRedirects: Record<UserRole, string> = {
      admin: '/dashboard/admin',
      agent: '/dashboard/agent',
      user: '/dashboard',
    };
    
    return <Navigate to={roleRedirects[user.role.name as UserRole]} replace />;
  }

  return <>{children}</>;
};
