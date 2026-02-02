import React from 'react';
import { Outlet } from 'react-router-dom';

interface PublicLayoutProps {
  children?: React.ReactNode;
}

export const PublicLayout: React.FC<PublicLayoutProps> = ({ children }) => {
  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center">
              <h1 className="text-2xl font-bold text-primary">MyProperty</h1>
            </div>
            <nav className="flex space-x-8">
              <a href="/" className="text-gray-700 hover:text-primary">Home</a>
              <a href="/properties" className="text-gray-700 hover:text-primary">Properties</a>
              <a href="/about" className="text-gray-700 hover:text-primary">About</a>
              <a href="/contact" className="text-gray-700 hover:text-primary">Contact</a>
            </nav>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="flex-1">
        <Outlet />
        {children}
      </main>

      {/* Footer */}
      <footer className="bg-gray-100 border-t">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div className="text-center text-gray-600">
            <p>&copy; 2024 MyProperty. All rights reserved.</p>
          </div>
        </div>
      </footer>
    </div>
  );
};
