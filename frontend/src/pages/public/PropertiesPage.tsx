import React from 'react';

export const PropertiesPage: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 className="text-3xl font-bold text-gray-900">Properties</h1>
        <p className="mt-4 text-gray-600">Browse our available properties</p>
        
        <div className="mt-8">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[1, 2, 3, 4, 5, 6].map((property) => (
              <div key={property} className="bg-white rounded-lg shadow-md overflow-hidden">
                <div className="h-48 bg-gray-200"></div>
                <div className="p-4">
                  <h3 className="text-lg font-semibold">Property {property}</h3>
                  <p className="text-gray-600 mt-2">Beautiful property with great amenities</p>
                  <p className="text-2xl font-bold text-primary mt-4">$250,000</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
