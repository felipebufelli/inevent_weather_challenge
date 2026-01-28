<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuth } from '@/composables/useAuth'
import {
  getCurrentWeather,
  getForecast,
  getAirQuality,
  getWeatherIconUrl,
  formatTime,
  isDay,
  type CurrentWeather,
  type ForecastResponse,
  type AirQuality
} from '@/services/weather'

const { user, logout } = useAuth()

// State
const searchCity = ref('')
const currentCity = ref('São Paulo')
const loading = ref(true)
const error = ref<string | null>(null)

const weather = ref<CurrentWeather | null>(null)
const forecast = ref<ForecastResponse | null>(null)
const airQuality = ref<AirQuality | null>(null)

// Computed
const isDaytime = computed(() => {
  if (!weather.value) return true
  return isDay(weather.value.dt, weather.value.sunrise, weather.value.sunset)
})

const sunriseTime = computed(() => {
  if (!weather.value) return '--:--'
  return formatTime(weather.value.sunrise, weather.value.timezone)
})

const sunsetTime = computed(() => {
  if (!weather.value) return '--:--'
  return formatTime(weather.value.sunset, weather.value.timezone)
})

const aqiColorClass = computed(() => {
  if (!airQuality.value) return ''
  return `aqi-${airQuality.value.color}`
})

// Methods
async function loadWeatherData(city: string) {
  loading.value = true
  error.value = null

  try {
    // Load all data in parallel
    const [weatherData, forecastData] = await Promise.all([
      getCurrentWeather(city),
      getForecast(city)
    ])

    weather.value = weatherData
    forecast.value = forecastData
    currentCity.value = weatherData.city

    // Load air quality with coordinates
    try {
      airQuality.value = await getAirQuality(weatherData.coord.lat, weatherData.coord.lon)
    } catch (e) {
      console.warn('Não foi possível carregar qualidade do ar')
    }
  } catch (err: any) {
    error.value = err.message || 'Erro ao carregar dados do clima'
    weather.value = null
    forecast.value = null
    airQuality.value = null
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  const city = searchCity.value.trim()
  if (city) {
    loadWeatherData(city)
    searchCity.value = ''
  }
}

function formatDate(timestamp: number): string {
  return new Date(timestamp * 1000).toLocaleDateString('pt-BR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long'
  })
}

onMounted(() => {
  loadWeatherData(currentCity.value)
})
</script>

<template>
  <div class="dashboard-wrapper">
    <!-- Background -->
    <div class="background-decoration">
      <div class="circle circle-1"></div>
      <div class="circle circle-2"></div>
      <div class="circle circle-3"></div>
    </div>

    <!-- Header -->
    <header class="app-header glass-card">
      <div class="header-content">
        <div class="logo">
          <svg class="logo-icon" viewBox="0 0 64 64" fill="none">
            <circle cx="26" cy="26" r="10" fill="currentColor" opacity="0.9"/>
            <path d="M26 8V4M26 48V44M44 26H48M4 26H8M38.5 13.5L41.3 10.7M10.7 41.3L13.5 38.5M38.5 38.5L41.3 41.3M10.7 10.7L13.5 13.5" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            <path d="M44 38C44 33.6 47.6 30 52 30C51.2 26.4 48 24 44 24C43.2 24 42.4 24.1 41.7 24.3C40.1 20.6 36.4 18 32 18C26.5 18 22 22.5 22 28C22 28.3 22 28.7 22.1 29C18.1 29.5 15 32.9 15 37C15 41.4 18.6 45 23 45H44C48.4 45 52 41.4 52 37C52 36.3 51.9 35.6 51.7 35" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="rgba(255,255,255,0.1)"/>
          </svg>
          <span class="logo-text">Previsão climática</span>
        </div>

        <!-- Search -->
        <form class="search-form" @submit.prevent="handleSearch">
          <div class="search-input-wrapper">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <path d="m21 21-4.35-4.35"/>
            </svg>
            <input
              v-model="searchCity"
              type="text"
              placeholder="Buscar cidade..."
              class="search-input"
            />
          </div>
          <button type="submit" class="search-btn">Buscar</button>
        </form>

        <div class="user-menu">
          <span class="user-name">{{ user?.name }}</span>
          <button class="logout-btn" @click="logout" title="Sair">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner-large"></div>
        <p>Carregando dados do clima...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state glass-card">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <path d="M12 8v4M12 16h.01"/>
        </svg>
        <p>{{ error }}</p>
        <button @click="loadWeatherData('São Paulo')">Tentar novamente</button>
      </div>

      <!-- Weather Dashboard -->
      <div v-else-if="weather" class="weather-dashboard">
        <!-- Current Weather Card -->
        <section class="current-weather glass-card">
          <div class="current-header">
            <div class="location-info">
              <h1 class="city-name">{{ weather.city }}, {{ weather.country }}</h1>
              <p class="current-date">{{ formatDate(weather.dt) }}</p>
            </div>
            <div class="current-temp-display">
              <img
                :src="getWeatherIconUrl(weather.weather.icon)"
                :alt="weather.weather.description"
                class="weather-icon-large"
              />
              <div class="temp-info">
                <span class="temp-main">{{ weather.temperature }}°</span>
                <span class="temp-unit">C</span>
              </div>
            </div>
          </div>

          <div class="current-description">
            <p class="weather-desc">{{ weather.weather.description }}</p>
            <p class="feels-like">Sensação térmica: {{ weather.feels_like }}°C</p>
          </div>

          <div class="current-details">
            <div class="detail-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
              </svg>
              <div class="detail-info">
                <span class="detail-value">{{ weather.humidity }}%</span>
                <span class="detail-label">Umidade</span>
              </div>
            </div>

            <div class="detail-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/>
              </svg>
              <div class="detail-info">
                <span class="detail-value">{{ weather.wind.speed }} km/h</span>
                <span class="detail-label">Vento {{ weather.wind.direction }}</span>
              </div>
            </div>

            <div class="detail-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
              </svg>
              <div class="detail-info">
                <span class="detail-value">{{ sunriseTime }}</span>
                <span class="detail-label">Nascer do sol</span>
              </div>
            </div>

            <div class="detail-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 18a5 5 0 0 0-10 0"/>
                <line x1="12" y1="9" x2="12" y2="2"/>
                <line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/>
                <line x1="1" y1="18" x2="3" y2="18"/>
                <line x1="21" y1="18" x2="23" y2="18"/>
                <line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/>
              </svg>
              <div class="detail-info">
                <span class="detail-value">{{ sunsetTime }}</span>
                <span class="detail-label">Pôr do sol</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Hourly Forecast -->
        <section v-if="forecast" class="hourly-forecast glass-card">
          <h2 class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
            Previsão por Hora
          </h2>
          <div class="hourly-scroll">
            <div
              v-for="hour in forecast.hourly"
              :key="hour.dt"
              class="hourly-item"
            >
              <span class="hour-time">{{ hour.time }}</span>
              <img
                :src="getWeatherIconUrl(hour.weather.icon)"
                :alt="hour.weather.description"
                class="hour-icon"
              />
              <span class="hour-temp">{{ hour.temperature }}°</span>
              <div class="hour-rain" v-if="hour.pop > 0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                </svg>
                <span>{{ hour.pop }}%</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Daily Forecast -->
        <section v-if="forecast" class="daily-forecast glass-card">
          <h2 class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Previsão 5 Dias
          </h2>
          <div class="daily-list">
            <div
              v-for="day in forecast.daily"
              :key="day.dt"
              class="daily-item"
            >
              <span class="day-name">{{ day.day_name }}</span>
              <div class="day-weather">
                <img
                  :src="getWeatherIconUrl(day.weather.icon)"
                  :alt="day.weather.description"
                  class="day-icon"
                />
                <span class="day-desc">{{ day.weather.description }}</span>
              </div>
              <div class="day-rain" v-if="day.pop > 0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                </svg>
                <span>{{ day.pop }}%</span>
              </div>
              <div class="day-temps">
                <span class="temp-max">{{ day.temp_max }}°</span>
                <div class="temp-bar">
                  <div class="temp-fill"></div>
                </div>
                <span class="temp-min">{{ day.temp_min }}°</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Weather Details & Air Quality Row -->
        <div class="details-row">
          <!-- Extra Weather Details -->
          <section class="weather-details glass-card">
            <h2 class="section-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>
              </svg>
              Detalhes
            </h2>
            <div class="details-grid">
              <div class="detail-card">
                <span class="detail-card-label">Pressão</span>
                <span class="detail-card-value">{{ weather.pressure }} hPa</span>
              </div>
              <div class="detail-card">
                <span class="detail-card-label">Visibilidade</span>
                <span class="detail-card-value">{{ weather.visibility ?? '--' }} km</span>
              </div>
              <div class="detail-card">
                <span class="detail-card-label">Nebulosidade</span>
                <span class="detail-card-value">{{ weather.clouds }}%</span>
              </div>
              <div class="detail-card">
                <span class="detail-card-label">Mínima / Máxima</span>
                <span class="detail-card-value">{{ weather.temp_min }}° / {{ weather.temp_max }}°</span>
              </div>
            </div>
          </section>

          <!-- Air Quality -->
          <section v-if="airQuality" class="air-quality glass-card">
            <h2 class="section-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 2c3 0 5 2 8 2s4-1 4-1v11s-1 1-4 1-5-2-8-2-4 1-4 1V2s1-1 4-1"/>
                <line x1="4" y1="22" x2="4" y2="15"/>
              </svg>
              Qualidade do Ar
            </h2>
            <div class="aqi-display" :class="aqiColorClass">
              <div class="aqi-value">{{ airQuality.aqi }}</div>
              <div class="aqi-label">{{ airQuality.label }}</div>
            </div>
            <div class="aqi-components">
              <div class="aqi-component">
                <span class="component-value">{{ airQuality.components.pm2_5 }}</span>
                <span class="component-label">PM2.5</span>
              </div>
              <div class="aqi-component">
                <span class="component-value">{{ airQuality.components.pm10 }}</span>
                <span class="component-label">PM10</span>
              </div>
              <div class="aqi-component">
                <span class="component-value">{{ airQuality.components.o3 }}</span>
                <span class="component-label">O₃</span>
              </div>
              <div class="aqi-component">
                <span class="component-value">{{ airQuality.components.no2 }}</span>
                <span class="component-label">NO₂</span>
              </div>
            </div>
          </section>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="app-footer">
      <p>Powered by <a href="https://github.com/felipebufelli" target="_blank" rel="noopener">Felipe Bufelli</a></p>
    </footer>
  </div>
</template>

<style scoped>
.dashboard-wrapper {
  min-height: 100vh;
  position: relative;
}

/* Background */
.background-decoration {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(169, 207, 226, 0.1);
  filter: blur(60px);
}

.circle-1 { width: 400px; height: 400px; top: -100px; right: -100px; animation: float 8s ease-in-out infinite; }
.circle-2 { width: 300px; height: 300px; bottom: -50px; left: -50px; animation: float 10s ease-in-out infinite reverse; }
.circle-3 { width: 200px; height: 200px; top: 40%; left: 30%; animation: float 6s ease-in-out infinite; }

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

/* Header */
.app-header {
  position: sticky;
  top: 0;
  z-index: 100;
  border-radius: 0;
  border-bottom: 1px solid var(--glass-border);
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: var(--spacing-md) var(--spacing-lg);
  display: flex;
  align-items: center;
  gap: var(--spacing-lg);
}

.logo {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.logo-icon { width: 32px; height: 32px; color: var(--primary-light); }
.logo-text { font-size: 1.25rem; font-weight: 700; color: var(--text-light); }

/* Search */
.search-form {
  flex: 1;
  max-width: 500px;
  display: flex;
  gap: var(--spacing-sm);
}

.search-input-wrapper {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 14px;
  width: 18px;
  height: 18px;
  color: var(--text-muted);
}

.search-input {
  width: 100%;
  padding: 12px 12px 12px 42px;
  font-size: 0.9375rem;
  font-family: inherit;
  color: var(--text-light);
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm);
  outline: none;
  transition: all var(--transition-fast);
}

.search-input::placeholder { color: var(--text-muted); }
.search-input:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: var(--primary-light);
}

.search-btn {
  padding: 12px 20px;
  font-size: 0.9375rem;
  font-weight: 600;
  font-family: inherit;
  color: var(--primary-dark);
  background: var(--primary-light);
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all var(--transition-fast);
  white-space: nowrap;
}

.search-btn:hover {
  background: var(--text-light);
  transform: translateY(-1px);
}

/* User Menu */
.user-menu {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  margin-left: auto;
}

.user-name {
  font-weight: 500;
  color: var(--text-light);
  text-transform: capitalize;
}

.logout-btn {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm);
  color: var(--text-muted);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.logout-btn:hover {
  background: rgba(229, 57, 53, 0.2);
  border-color: var(--error);
  color: var(--error);
}

.logout-btn svg { width: 18px; height: 18px; }

/* Main Content */
.main-content {
  position: relative;
  z-index: 1;
  max-width: 1400px;
  margin: 0 auto;
  padding: var(--spacing-xl) var(--spacing-lg);
}

/* Loading & Error States */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-xl) 0;
  gap: var(--spacing-md);
  color: var(--text-muted);
}

.loading-spinner-large {
  width: 48px;
  height: 48px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top-color: var(--primary-light);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: var(--spacing-xl);
  text-align: center;
  gap: var(--spacing-md);
  color: var(--error);
}

.error-state svg { width: 48px; height: 48px; }

.error-state button {
  padding: 12px 24px;
  background: var(--primary-light);
  color: var(--primary-dark);
  border: none;
  border-radius: var(--radius-sm);
  font-weight: 600;
  cursor: pointer;
}

/* Weather Dashboard Grid */
.weather-dashboard {
  display: grid;
  gap: var(--spacing-lg);
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Section Title */
.section-title {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-light);
  margin-bottom: var(--spacing-md);
}

.section-title svg {
  width: 20px;
  height: 20px;
  color: var(--primary-light);
}

/* Current Weather */
.current-weather {
  padding: var(--spacing-xl);
}

.current-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: var(--spacing-lg);
}

.city-name {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-light);
  margin-bottom: 4px;
}

.current-date {
  color: var(--text-muted);
  text-transform: capitalize;
}

.current-temp-display {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.weather-icon-large {
  width: 100px;
  height: 100px;
}

.temp-info {
  display: flex;
  align-items: flex-start;
}

.temp-main {
  font-size: 5rem;
  font-weight: 700;
  line-height: 1;
  color: var(--text-light);
}

.temp-unit {
  font-size: 2rem;
  font-weight: 500;
  color: var(--primary-light);
  margin-top: 12px;
}

.current-description {
  margin-bottom: var(--spacing-lg);
  padding-bottom: var(--spacing-lg);
  border-bottom: 1px solid var(--glass-border);
}

.weather-desc {
  font-size: 1.25rem;
  color: var(--text-light);
  text-transform: capitalize;
  margin-bottom: 4px;
}

.feels-like {
  color: var(--text-muted);
}

.current-details {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--spacing-md);
}

.detail-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.detail-item svg {
  width: 24px;
  height: 24px;
  color: var(--primary-light);
  flex-shrink: 0;
}

.detail-info {
  display: flex;
  flex-direction: column;
}

.detail-value {
  font-weight: 600;
  color: var(--text-light);
}

.detail-label {
  font-size: 0.75rem;
  color: var(--text-muted);
}

/* Hourly Forecast */
.hourly-forecast {
  padding: var(--spacing-lg);
}

.hourly-scroll {
  display: flex;
  gap: var(--spacing-md);
  overflow-x: auto;
  padding-bottom: var(--spacing-sm);
  scrollbar-width: thin;
  scrollbar-color: var(--primary-light) transparent;
}

.hourly-scroll::-webkit-scrollbar { height: 6px; }
.hourly-scroll::-webkit-scrollbar-track { background: transparent; }
.hourly-scroll::-webkit-scrollbar-thumb { background: var(--primary-light); border-radius: 3px; }

.hourly-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: var(--spacing-md);
  min-width: 80px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}

.hourly-item:hover {
  background: rgba(255, 255, 255, 0.1);
}

.hour-time {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.hour-icon {
  width: 48px;
  height: 48px;
}

.hour-temp {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-light);
}

.hour-rain {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.75rem;
  color: #64b5f6;
}

.hour-rain svg {
  width: 12px;
  height: 12px;
}

/* Daily Forecast */
.daily-forecast {
  padding: var(--spacing-lg);
}

.daily-list {
  display: flex;
  flex-direction: column;
}

.daily-item {
  display: grid;
  grid-template-columns: 100px 1fr auto 180px;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-md) 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.daily-item:last-child {
  border-bottom: none;
}

.day-name {
  font-weight: 500;
  color: var(--text-light);
}

.day-weather {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.day-icon {
  width: 40px;
  height: 40px;
}

.day-desc {
  color: var(--text-muted);
  font-size: 0.875rem;
  text-transform: capitalize;
}

.day-rain {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.875rem;
  color: #64b5f6;
}

.day-rain svg {
  width: 14px;
  height: 14px;
}

.day-temps {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.temp-max {
  font-weight: 600;
  color: var(--text-light);
  width: 35px;
  text-align: right;
}

.temp-min {
  color: var(--text-muted);
  width: 35px;
}

.temp-bar {
  flex: 1;
  height: 4px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
  overflow: hidden;
}

.temp-fill {
  width: 60%;
  height: 100%;
  background: linear-gradient(90deg, var(--primary-light), #ffb74d);
  border-radius: 2px;
}

/* Details Row */
.details-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-lg);
}

/* Weather Details */
.weather-details {
  padding: var(--spacing-lg);
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-md);
}

.detail-card {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: var(--spacing-md);
  background: rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-sm);
}

.detail-card-label {
  font-size: 0.75rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-card-value {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-light);
}

/* Air Quality */
.air-quality {
  padding: var(--spacing-lg);
}

.aqi-display {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-md);
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-md);
}

.aqi-display.aqi-good { background: rgba(76, 175, 80, 0.2); }
.aqi-display.aqi-fair { background: rgba(255, 235, 59, 0.2); }
.aqi-display.aqi-moderate { background: rgba(255, 152, 0, 0.2); }
.aqi-display.aqi-poor { background: rgba(244, 67, 54, 0.2); }
.aqi-display.aqi-very_poor { background: rgba(156, 39, 176, 0.2); }

.aqi-value {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--text-light);
}

.aqi-label {
  font-size: 1.25rem;
  font-weight: 500;
  color: var(--text-light);
}

.aqi-components {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--spacing-sm);
}

.aqi-component {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  padding: var(--spacing-sm);
  background: rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-sm);
}

.component-value {
  font-weight: 600;
  color: var(--text-light);
}

.component-label {
  font-size: 0.75rem;
  color: var(--text-muted);
}

/* Footer */
.app-footer {
  position: relative;
  z-index: 1;
  text-align: center;
  padding: var(--spacing-lg);
  color: var(--text-muted);
  font-size: 0.875rem;
}

.app-footer a {
  color: var(--primary-light);
  text-decoration: none;
}

.app-footer a:hover {
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 1024px) {
  .details-row {
    grid-template-columns: 1fr;
  }

  .current-details {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .header-content {
    flex-wrap: wrap;
    gap: var(--spacing-md);
  }

  .search-form {
    order: 3;
    max-width: 100%;
    flex: 1 1 100%;
  }

  .user-name {
    display: none;
  }

  .current-header {
    flex-direction: column;
    gap: var(--spacing-md);
  }

  .temp-main {
    font-size: 4rem;
  }

  .daily-item {
    grid-template-columns: 80px 1fr auto;
  }

  .day-temps {
    display: none;
  }

  .aqi-components {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .current-details {
    grid-template-columns: 1fr;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }
}
</style>
