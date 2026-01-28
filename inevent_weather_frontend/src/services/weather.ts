import { API_URL } from '@/config/api'

// ============ Types ============

export interface Coordinates {
  lat: number
  lon: number
}

export interface Wind {
  speed: number
  deg?: number
  direction: string
}

export interface WeatherCondition {
  main: string
  description: string
  icon: string
}

export interface CurrentWeather {
  city: string
  country: string
  coord: Coordinates
  temperature: number
  feels_like: number
  temp_min: number
  temp_max: number
  humidity: number
  pressure: number
  visibility: number | null
  wind: Wind
  clouds: number
  weather: WeatherCondition
  sunrise: number
  sunset: number
  timezone: number
  dt: number
}

export interface HourlyForecast {
  dt: number
  time: string
  temperature: number
  feels_like: number
  humidity: number
  weather: WeatherCondition
  wind: Wind
  pop: number
  rain: number
  clouds: number
}

export interface DailyForecast {
  dt: number
  date: string
  day_name: string
  temp_min: number
  temp_max: number
  humidity: number
  weather: WeatherCondition
  pop: number
  wind_speed: number
}

export interface ForecastResponse {
  city: string
  country: string
  coord: Coordinates
  timezone: number
  hourly: HourlyForecast[]
  daily: DailyForecast[]
}

export interface AirQualityComponents {
  co: number
  no: number
  no2: number
  o3: number
  so2: number
  pm2_5: number
  pm10: number
  nh3: number
}

export interface AirQuality {
  aqi: number
  label: string
  color: string
  components: AirQualityComponents
  dt: number
}

// ============ API Functions ============

export async function getCurrentWeather(city: string): Promise<CurrentWeather> {
  const response = await fetch(`${API_URL}/weather?city=${encodeURIComponent(city)}`)

  if (!response.ok) {
    const error = await response.json()
    throw new Error(error.message || 'Erro ao buscar clima')
  }

  return response.json()
}

export async function getForecast(city: string): Promise<ForecastResponse> {
  const response = await fetch(`${API_URL}/forecast?city=${encodeURIComponent(city)}`)

  if (!response.ok) {
    const error = await response.json()
    throw new Error(error.message || 'Erro ao buscar previsão')
  }

  return response.json()
}

export async function getAirQuality(lat: number, lon: number): Promise<AirQuality> {
  const response = await fetch(`${API_URL}/air-quality?lat=${lat}&lon=${lon}`)

  if (!response.ok) {
    const error = await response.json()
    throw new Error(error.message || 'Erro ao buscar qualidade do ar')
  }

  return response.json()
}

// ============ Utility Functions ============

export function getWeatherIconUrl(icon: string): string {
  return `https://openweathermap.org/img/wn/${icon}@2x.png`
}

export function formatTime(timestamp: number, timezone: number = 0): string {
  const date = new Date((timestamp + timezone) * 1000)
  return date.toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'UTC'
  })
}

export function isDay(current: number, sunrise: number, sunset: number): boolean {
  return current >= sunrise && current < sunset
}
