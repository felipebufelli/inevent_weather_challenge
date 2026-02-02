import { describe, it, expect, vi, beforeEach } from 'vitest'
import {
  getCurrentWeather,
  getForecast,
  getAirQuality,
  getWeatherIconUrl,
  formatTime,
  isDay,
} from './weather'

// vi.mock is hoisted - use string literal inside factory (no top-level variables)
vi.mock('@/config/api', () => ({
  API_URL: 'http://test.api',
}))

describe('weather service', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn())
  })

  describe('getCurrentWeather', () => {
    it('returns weather data with Bearer token', async () => {
      const mockWeather = {
        city: 'São Paulo',
        country: 'BR',
        coord: { lat: -23.5, lon: -46.6 },
        temperature: 25,
        feels_like: 26,
        temp_min: 22,
        temp_max: 28,
        humidity: 65,
        pressure: 1015,
        visibility: 10,
        wind: { speed: 12, direction: 'NE' },
        clouds: 50,
        weather: { main: 'Clouds', description: 'nublado', icon: '04d' },
        sunrise: 1,
        sunset: 2,
        timezone: -10800,
        dt: 3,
      }
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve(mockWeather),
      } as Response)

      const result = await getCurrentWeather('São Paulo', 'auth-token')

      expect(result).toEqual(mockWeather)
      expect(fetch).toHaveBeenCalledWith(
        `http://test.api/weather?city=${encodeURIComponent('São Paulo')}`,
        expect.objectContaining({
          headers: expect.objectContaining({
            'Content-Type': 'application/json',
            Authorization: 'Bearer auth-token',
          }),
        })
      )
    })

    it('throws on error response', async () => {
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: false,
        json: () => Promise.resolve({ message: 'Cidade não encontrada' }),
      } as Response)

      await expect(
        getCurrentWeather('Invalid', 'token')
      ).rejects.toThrow('Cidade não encontrada')
    })
  })

  describe('getForecast', () => {
    it('returns forecast with Bearer token', async () => {
      const mockForecast = {
        city: 'SP',
        country: 'BR',
        coord: {},
        timezone: -10800,
        hourly: [],
        daily: [],
      }
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve(mockForecast),
      } as Response)

      const result = await getForecast('São Paulo', 'token')

      expect(result).toEqual(mockForecast)
      expect(fetch).toHaveBeenCalledWith(
        expect.stringContaining('/forecast'),
        expect.objectContaining({
          headers: expect.objectContaining({ Authorization: 'Bearer token' }),
        })
      )
    })

    it('throws on error response', async () => {
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: false,
        json: () => Promise.resolve({ message: 'Erro ao buscar previsão' }),
      } as Response)

      await expect(getForecast('X', 't')).rejects.toThrow(
        'Erro ao buscar previsão'
      )
    })
  })

  describe('getAirQuality', () => {
    it('returns air quality with Bearer token', async () => {
      const mockAq = {
        aqi: 1,
        label: 'Bom',
        color: 'good',
        components: {},
        dt: 1,
      }
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve(mockAq),
      } as Response)

      const result = await getAirQuality(-23.5, -46.6, 't')

      expect(result).toEqual(mockAq)
      expect(fetch).toHaveBeenCalledWith(
        'http://test.api/air-quality?lat=-23.5&lon=-46.6',
        expect.objectContaining({
          headers: expect.objectContaining({ Authorization: 'Bearer t' }),
        })
      )
    })

    it('throws on error response', async () => {
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: false,
        json: () =>
          Promise.resolve({ message: 'Erro ao buscar qualidade do ar' }),
      } as Response)

      await expect(getAirQuality(0, 0, 't')).rejects.toThrow(
        'Erro ao buscar qualidade do ar'
      )
    })
  })

  describe('getWeatherIconUrl', () => {
    it('returns OpenWeatherMap icon URL', () => {
      expect(getWeatherIconUrl('04d')).toBe(
        'https://openweathermap.org/img/wn/04d@2x.png'
      )
      expect(getWeatherIconUrl('01n')).toBe(
        'https://openweathermap.org/img/wn/01n@2x.png'
      )
    })
  })

  describe('formatTime', () => {
    it('formats timestamp with timezone', () => {
      const result = formatTime(0, 0)
      expect(typeof result).toBe('string')
      expect(result).toMatch(/\d{1,2}:\d{2}/)
    })

    it('uses default timezone 0 when not provided', () => {
      const result = formatTime(3600)
      expect(typeof result).toBe('string')
    })
  })

  describe('isDay', () => {
    it('returns true when current is between sunrise and sunset', () => {
      expect(isDay(100, 50, 150)).toBe(true)
      expect(isDay(50, 50, 150)).toBe(true)
      expect(isDay(149, 50, 150)).toBe(true)
    })

    it('returns false when current is before sunrise', () => {
      expect(isDay(40, 50, 150)).toBe(false)
    })

    it('returns false when current is at or after sunset', () => {
      expect(isDay(150, 50, 150)).toBe(false)
      expect(isDay(200, 50, 150)).toBe(false)
    })
  })
})
