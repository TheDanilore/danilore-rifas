<template>
  <div class="admin-ventas">
    <AdminHeader />
    
    <!-- Hero Section -->
    <section class="admin-hero">
      <div class="container">
        <div class="hero-content">
          <h1 class="hero-title">
            <i class="fas fa-chart-bar"></i>
            Reportes de Ventas
          </h1>
          <p class="hero-subtitle">
            Analiza las ventas y estadísticas del negocio
          </p>
        </div>
        
        <div class="hero-actions">
          <div class="date-range-picker">
            <input type="date" v-model="dateRange.start" class="date-input" />
            <span class="date-separator">a</span>
            <input type="date" v-model="dateRange.end" class="date-input" />
          </div>
          <button @click="exportReport" class="btn btn-outline btn-lg">
            <i class="fas fa-download"></i>
            Exportar Reporte
          </button>
        </div>
      </div>
    </section>

    <!-- Revenue Stats -->
    <section class="revenue-section">
      <div class="container">
        <div class="revenue-grid">
          <div class="revenue-card featured">
            <div class="revenue-icon">
              <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="revenue-content">
              <h3 class="revenue-amount">S/. {{ totalIngresos.toLocaleString() }}</h3>
              <p class="revenue-label">Ingresos Totales</p>
              <div class="revenue-period">Este mes</div>
              <span class="revenue-change positive">
                <i class="fas fa-arrow-up"></i>
                +23.5% vs mes anterior
              </span>
            </div>
          </div>
          
          <div class="revenue-card">
            <div class="revenue-icon sales">
              <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="revenue-content">
              <h3 class="revenue-amount">{{ totalVentas }}</h3>
              <p class="revenue-label">Ventas Totales</p>
              <span class="revenue-change positive">
                <i class="fas fa-arrow-up"></i>
                +18.2%
              </span>
            </div>
          </div>
          
          <div class="revenue-card">
            <div class="revenue-icon tickets">
              <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="revenue-content">
              <h3 class="revenue-amount">{{ ticketsVendidos }}</h3>
              <p class="revenue-label">Tickets Vendidos</p>
              <span class="revenue-change positive">
                <i class="fas fa-arrow-up"></i>
                +15.7%
              </span>
            </div>
          </div>
          
          <div class="revenue-card">
            <div class="revenue-icon average">
              <i class="fas fa-chart-line"></i>
            </div>
            <div class="revenue-content">
              <h3 class="revenue-amount">S/. {{ promedioVenta }}</h3>
              <p class="revenue-label">Promedio por Venta</p>
              <span class="revenue-change positive">
                <i class="fas fa-arrow-up"></i>
                +8.3%
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Charts Section -->
    <section class="charts-section">
      <div class="container">
        <div class="charts-grid">
          <!-- Sales Chart -->
          <div class="chart-card large">
            <div class="chart-header">
              <h3>Ventas de los Últimos 30 Días</h3>
              <div class="chart-filters">
                <button class="filter-btn active">30D</button>
                <button class="filter-btn">90D</button>
                <button class="filter-btn">1A</button>
              </div>
            </div>
            <div class="chart-placeholder">
              <i class="fas fa-chart-area"></i>
              <p>Gráfico de ventas temporales</p>
              <div class="mock-chart">
                <div class="chart-bars">
                  <div class="chart-bar" style="height: 60%"></div>
                  <div class="chart-bar" style="height: 80%"></div>
                  <div class="chart-bar" style="height: 45%"></div>
                  <div class="chart-bar" style="height: 90%"></div>
                  <div class="chart-bar" style="height: 70%"></div>
                  <div class="chart-bar" style="height: 55%"></div>
                  <div class="chart-bar" style="height: 85%"></div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Top Rifas -->
          <div class="chart-card">
            <div class="chart-header">
              <h3>Top Rifas por Ingresos</h3>
            </div>
            <div class="top-rifas">
              <div
                v-for="(rifa, index) in topRifas"
                :key="rifa.id"
                class="top-rifa-item"
              >
                <div class="rifa-rank">{{ index + 1 }}</div>
                <div class="rifa-info">
                  <h4 class="rifa-name">{{ rifa.nombre }}</h4>
                  <p class="rifa-sales">{{ rifa.ventasCount }} ventas</p>
                </div>
                <div class="rifa-revenue">
                  S/. {{ rifa.ingresos.toLocaleString() }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sales by Method -->
    <section class="payment-methods-section">
      <div class="container">
        <div class="methods-grid">
          <div class="methods-card">
            <h3>Métodos de Pago</h3>
            <div class="methods-list">
              <div
                v-for="method in paymentMethods"
                :key="method.name"
                class="method-item"
              >
                <div class="method-info">
                  <div class="method-icon" :style="{ background: method.color }">
                    <i :class="method.icon"></i>
                  </div>
                  <div>
                    <h4 class="method-name">{{ method.name }}</h4>
                    <p class="method-count">{{ method.transactions }} transacciones</p>
                  </div>
                </div>
                <div class="method-amount">
                  <span class="amount">S/. {{ method.amount.toLocaleString() }}</span>
                  <span class="percentage">{{ method.percentage }}%</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="methods-card">
            <h3>Ventas por Hora</h3>
            <div class="hourly-chart">
              <div
                v-for="hour in hourlyData"
                :key="hour.hour"
                class="hour-bar"
              >
                <div class="bar-fill" :style="{ height: hour.percentage + '%' }"></div>
                <span class="hour-label">{{ hour.hour }}h</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent Transactions -->
    <section class="transactions-section">
      <div class="container">
        <div class="transactions-card">
          <div class="transactions-header">
            <h3>Transacciones Recientes</h3>
            <button class="btn btn-ghost btn-sm">
              <i class="fas fa-external-link-alt"></i>
              Ver Todas
            </button>
          </div>
          
          <div class="transactions-list">
            <div
              v-for="transaction in recentTransactions"
              :key="transaction.id"
              class="transaction-item"
            >
              <div class="transaction-icon" :class="transaction.status">
                <i :class="transaction.icon"></i>
              </div>
              <div class="transaction-details">
                <h4 class="transaction-user">{{ transaction.usuario }}</h4>
                <p class="transaction-rifa">{{ transaction.rifa }}</p>
                <span class="transaction-time">{{ formatRelativeTime(transaction.fecha) }}</span>
              </div>
              <div class="transaction-method">
                <span class="method-badge" :class="transaction.metodo.toLowerCase()">
                  {{ transaction.metodo }}
                </span>
              </div>
              <div class="transaction-amount">
                <span class="amount">S/. {{ transaction.monto }}</span>
                <span class="status" :class="transaction.status">
                  {{ transaction.estadoTexto }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <AdminFooter />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import AdminHeader from '@/components/admin/AdminHeader.vue'
import AdminFooter from '@/components/admin/AdminFooter.vue'

export default {
  name: 'AdminVentas',
  components: {
    AdminHeader,
    AdminFooter
  },
  setup() {
    const dateRange = ref({
      start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      end: new Date().toISOString().split('T')[0]
    })

    const salesData = ref({
      totalIngresos: 45230,
      totalVentas: 892,
      ticketsVendidos: 3420,
      promedioVenta: 51
    })

    const topRifas = ref([
      {
        id: 'RF001',
        nombre: 'iPhone 15 Pro Max',
        ventasCount: 450,
        ingresos: 6750
      },
      {
        id: 'RF002',
        nombre: 'MacBook Pro M3',
        ventasCount: 280,
        ingresos: 7000
      },
      {
        id: 'RF003',
        nombre: 'PlayStation 5',
        ventasCount: 320,
        ingresos: 6400
      },
      {
        id: 'RF004',
        nombre: 'AirPods Pro',
        ventasCount: 180,
        ingresos: 1800
      }
    ])

    const paymentMethods = ref([
      {
        name: 'Yape',
        icon: 'fas fa-mobile-alt',
        color: 'linear-gradient(135deg, #8B1538, #B91C5C)',
        transactions: 567,
        amount: 28350,
        percentage: 62.7
      },
      {
        name: 'Plin',
        icon: 'fas fa-credit-card',
        color: 'linear-gradient(135deg, #059669, #10B981)',
        transactions: 234,
        amount: 11700,
        percentage: 25.9
      },
      {
        name: 'Transferencia',
        icon: 'fas fa-university',
        color: 'linear-gradient(135deg, #2563EB, #3B82F6)',
        transactions: 91,
        amount: 5180,
        percentage: 11.4
      }
    ])

    const hourlyData = ref([
      { hour: 0, percentage: 15 },
      { hour: 6, percentage: 25 },
      { hour: 12, percentage: 85 },
      { hour: 18, percentage: 70 },
      { hour: 24, percentage: 30 }
    ])

    const recentTransactions = ref([
      {
        id: 'TXN001',
        usuario: 'Carlos Mendoza',
        rifa: 'iPhone 15 Pro Max - Ticket #234',
        monto: 15,
        metodo: 'Yape',
        fecha: '2024-02-10T14:30:00Z',
        status: 'success',
        estadoTexto: 'Completado',
        icon: 'fas fa-check-circle'
      },
      {
        id: 'TXN002',
        usuario: 'María González',
        rifa: 'MacBook Pro M3 - Ticket #156',
        monto: 25,
        metodo: 'Plin',
        fecha: '2024-02-10T13:45:00Z',
        status: 'success',
        estadoTexto: 'Completado',
        icon: 'fas fa-check-circle'
      },
      {
        id: 'TXN003',
        usuario: 'Luis Ramírez',
        rifa: 'PlayStation 5 - Ticket #089',
        monto: 20,
        metodo: 'Yape',
        fecha: '2024-02-10T12:20:00Z',
        status: 'pending',
        estadoTexto: 'Pendiente',
        icon: 'fas fa-clock'
      },
      {
        id: 'TXN004',
        usuario: 'Ana Torres',
        rifa: 'AirPods Pro - Ticket #067',
        monto: 10,
        metodo: 'Transferencia',
        fecha: '2024-02-10T11:15:00Z',
        status: 'success',
        estadoTexto: 'Completado',
        icon: 'fas fa-check-circle'
      }
    ])

    const totalIngresos = computed(() => salesData.value.totalIngresos)
    const totalVentas = computed(() => salesData.value.totalVentas)
    const ticketsVendidos = computed(() => salesData.value.ticketsVendidos)
    const promedioVenta = computed(() => salesData.value.promedioVenta)

    const formatRelativeTime = (dateString) => {
      const date = new Date(dateString)
      const now = new Date()
      const diffMs = now - date
      const diffMinutes = Math.floor(diffMs / (1000 * 60))
      const diffHours = Math.floor(diffMinutes / 60)

      if (diffHours > 0) {
        return `Hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`
      } else if (diffMinutes > 0) {
        return `Hace ${diffMinutes} minuto${diffMinutes > 1 ? 's' : ''}`
      } else {
        return 'Ahora mismo'
      }
    }

    const exportReport = () => {
      console.log('Exportar reporte de ventas')
    }

    onMounted(() => {
      console.log('Admin Ventas cargado')
    })

    return {
      dateRange,
      totalIngresos,
      totalVentas,
      ticketsVendidos,
      promedioVenta,
      topRifas,
      paymentMethods,
      hourlyData,
      recentTransactions,
      formatRelativeTime,
      exportReport
    }
  }
}
</script>

<style scoped>
.admin-ventas {
  min-height: 100vh;
  background: var(--gray-50);
  display: flex;
  flex-direction: column;
}

.admin-hero {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: white;
  padding: 3rem 0;
}

.hero-content {
  text-align: center;
  margin-bottom: 2rem;
}

.hero-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.hero-subtitle {
  font-size: 1.125rem;
  opacity: 0.9;
}

.hero-actions {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 2rem;
}

.date-range-picker {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: rgba(255, 255, 255, 0.1);
  padding: 0.75rem 1.5rem;
  border-radius: var(--border-radius-full);
}

.date-input {
  background: transparent;
  border: none;
  color: white;
  font-size: 1rem;
}

.date-input:focus {
  outline: none;
}

.date-separator {
  color: rgba(255, 255, 255, 0.7);
  font-weight: 600;
}

.revenue-section {
  padding: 3rem 0;
}

.revenue-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 2rem;
}

.revenue-card {
  background: white;
  padding: 2rem;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
  transition: transform 0.3s ease;
}

.revenue-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.revenue-card.featured {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: white;
}

.revenue-icon {
  width: 4rem;
  height: 4rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
}

.revenue-card:not(.featured) .revenue-icon {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
}

.revenue-icon.sales {
  background: linear-gradient(135deg, var(--success-green), #34d399);
}

.revenue-icon.tickets {
  background: linear-gradient(135deg, var(--primary-blue), #3b82f6);
}

.revenue-icon.average {
  background: linear-gradient(135deg, var(--accent-orange), #f97316);
}

.revenue-amount {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.revenue-card:not(.featured) .revenue-amount {
  color: var(--gray-800);
}

.revenue-label {
  font-size: 1rem;
  margin-bottom: 0.5rem;
  opacity: 0.9;
}

.revenue-card:not(.featured) .revenue-label {
  color: var(--gray-600);
}

.revenue-period {
  font-size: 0.875rem;
  opacity: 0.8;
  margin-bottom: 1rem;
}

.revenue-change {
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.revenue-change.positive {
  color: #34d399;
}

.revenue-card:not(.featured) .revenue-change.positive {
  color: var(--success-green);
}

.charts-section {
  padding: 0 0 3rem 0;
}

.charts-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
}

.chart-card {
  background: white;
  padding: 2rem;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--gray-200);
}

.chart-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
}

.chart-filters {
  display: flex;
  gap: 0.5rem;
}

.filter-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--gray-300);
  background: white;
  border-radius: var(--border-radius);
  cursor: pointer;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.filter-btn.active,
.filter-btn:hover {
  background: var(--primary-purple);
  color: white;
  border-color: var(--primary-purple);
}

.chart-placeholder {
  height: 300px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--gray-400);
  position: relative;
}

.chart-placeholder i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.mock-chart {
  position: absolute;
  bottom: 2rem;
  left: 2rem;
  right: 2rem;
}

.chart-bars {
  display: flex;
  align-items: end;
  gap: 1rem;
  height: 100px;
}

.chart-bar {
  flex: 1;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: 4px;
  min-height: 20px;
  opacity: 0.8;
}

.top-rifas {
  space-y: 1rem;
}

.top-rifa-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: var(--border-radius);
  margin-bottom: 1rem;
}

.rifa-rank {
  width: 2rem;
  height: 2rem;
  background: var(--primary-purple);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.875rem;
}

.rifa-info {
  flex: 1;
}

.rifa-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.rifa-sales {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.rifa-revenue {
  font-weight: 700;
  color: var(--success-green);
}

.payment-methods-section {
  padding: 0 0 3rem 0;
}

.methods-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.methods-card {
  background: white;
  padding: 2rem;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
}

.methods-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 2rem;
}

.methods-list {
  space-y: 1rem;
}

.method-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: var(--border-radius);
  margin-bottom: 1rem;
}

.method-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.method-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
}

.method-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.method-count {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.method-amount {
  text-align: right;
}

.amount {
  display: block;
  font-weight: 700;
  color: var(--gray-800);
}

.percentage {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.hourly-chart {
  display: flex;
  align-items: end;
  gap: 1rem;
  height: 200px;
  padding: 1rem 0;
}

.hour-bar {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 100%;
}

.bar-fill {
  width: 100%;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: 4px;
  margin-bottom: 0.5rem;
  min-height: 10px;
}

.hour-label {
  font-size: 0.75rem;
  color: var(--gray-600);
}

.transactions-section {
  padding: 0 0 3rem 0;
  flex: 1;
}

.transactions-card {
  background: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
  overflow: hidden;
}

.transactions-header {
  padding: 2rem;
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.transactions-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
}

.transactions-list {
  padding: 1rem 0;
}

.transaction-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 2rem;
  border-bottom: 1px solid var(--gray-100);
  transition: background 0.3s ease;
}

.transaction-item:hover {
  background: var(--gray-50);
}

.transaction-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
}

.transaction-icon.success {
  background: var(--success-green);
}

.transaction-icon.pending {
  background: var(--warning-yellow);
}

.transaction-details {
  flex: 1;
}

.transaction-user {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.transaction-rifa {
  font-size: 0.875rem;
  color: var(--gray-600);
  margin-bottom: 0.25rem;
}

.transaction-time {
  font-size: 0.75rem;
  color: var(--gray-500);
}

.method-badge {
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  font-size: 0.75rem;
  font-weight: 600;
}

.method-badge.yape {
  background: rgba(139, 21, 56, 0.1);
  color: #8B1538;
}

.method-badge.plin {
  background: rgba(5, 150, 105, 0.1);
  color: #059669;
}

.method-badge.transferencia {
  background: rgba(37, 99, 235, 0.1);
  color: #2563EB;
}

.transaction-amount {
  text-align: right;
}

.transaction-amount .amount {
  font-weight: 700;
  font-size: 1rem;
  color: var(--gray-800);
}

.transaction-amount .status {
  font-size: 0.75rem;
  font-weight: 600;
}

.transaction-amount .status.success {
  color: var(--success-green);
}

.transaction-amount .status.pending {
  color: var(--warning-yellow);
}

@media (max-width: 1024px) {
  .revenue-grid {
    grid-template-columns: 1fr 1fr;
  }

  .charts-grid,
  .methods-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem;
    flex-direction: column;
    gap: 0.5rem;
  }

  .hero-actions {
    flex-direction: column;
    gap: 1rem;
  }

  .revenue-grid {
    grid-template-columns: 1fr;
  }

  .transaction-item {
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
  }
}
</style>
