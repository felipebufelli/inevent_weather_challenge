import { describe, it, expect } from 'vitest'
import { API_URL, API_BASE_URL } from '@/config/api'

describe('api config', () => {
  it('exports API_BASE_URL (may be undefined when VITE_API_BASE_URL is not set)', () => {
    // In Vitest, import.meta.env.VITE_API_BASE_URL may be undefined
    expect(API_BASE_URL === undefined || typeof API_BASE_URL === 'string').toBe(true)
  })

  it('exports API_URL with fallback when env not set', () => {
    expect(API_URL).toBeDefined()
    expect(typeof API_URL).toBe('string')
    expect(API_URL.length).toBeGreaterThan(0)
    expect(API_URL).toBe(API_BASE_URL || 'http://localhost:8000/api')
  })
})
