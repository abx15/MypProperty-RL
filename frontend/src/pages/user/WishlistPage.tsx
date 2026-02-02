import React from 'react';

export const WishlistPage: React.FC = () => {
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">My Wishlist</h1>
      <div className="bg-white p-6 rounded-lg shadow">
        <p>Your saved properties will appear here...</p>
      </div>
    </div>
  );
};
