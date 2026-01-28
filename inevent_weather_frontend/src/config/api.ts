/**
 * API Configuration
 * Centralized configuration for API endpoints
 */

export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

// Validate that the environment variable is set
if (!API_BASE_URL) {
  console.warn('Warning: VITE_API_BASE_URL is not defined. Using default: http://localhost:8000/api')
}

export const API_URL = API_BASE_URL || 'http://localhost:8000/api'
