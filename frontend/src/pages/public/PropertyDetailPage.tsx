import React from 'react';

export const PropertyDetailPage: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 className="text-3xl font-bold text-gray-900">Property Details</h1>
        <div className="mt-8 bg-white rounded-lg shadow-md p-6">
          <div className="h-64 bg-gray-200 rounded-lg mb-6"></div>
          <h2 className="text-2xl font-bold">Beautiful Family Home</h2>
          <p className="text-gray-600 mt-2">Detailed property information coming soon...</p>
        </div>
      </div>
    </div>
  );
};
