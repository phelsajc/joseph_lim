<template>
  <div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <div class="welcome-section">
          <h1 class="dashboard-title">Dashboard Overview</h1>
          <p class="dashboard-subtitle">Welcome back! Here's what's happening with your medical practice today.</p>
        </div>
        <div class="header-actions">
          <el-button type="primary" icon="el-icon-plus" @click="handleQuickAction">
            Quick Action
          </el-button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-section">
      <panel-group 
        @handleSetLineChartData="handleSetLineChartData" 
        :total_patients="count_px" 
        :total_meds="count_meds" 
        :total_dxs="count_dx" 
        :total_appts="count_appt"
      />
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
      <el-row :gutter="24">
        <el-col :xs="24" :sm="24" :md="24" :lg="16" :xl="16">
          <div class="chart-card">
            <div class="chart-header">
              <h3 class="chart-title">Monthly Census Report</h3>
              <div class="chart-actions">
                <el-button-group>
                  <el-button size="mini" @click="refreshChart">Refresh</el-button>
                  <el-button size="mini" icon="el-icon-download" @click="exportChart">Export</el-button>
                </el-button-group>
              </div>
            </div>
            <div class="chart-content">
              <apexchart
                ref="radar"
                type="line"
                height="350"
                :options="chartOptions"
                :series="series"
              ></apexchart>
            </div>
          </div>
      </el-col>
         <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
           <div class="quick-stats-card">
             <h3 class="card-title">Today's Activity</h3>
             <div class="quick-stats">
               <div class="stat-item">
                 <div class="stat-icon patients">
                   <i class="el-icon-user"></i>
                 </div>
                 <div class="stat-content">
                   <span class="stat-label">Today's Patients</span>
                   <span class="stat-value">{{ todayspxs.length || 0 }}</span>
                 </div>
               </div>
               <div class="stat-item">
                 <div class="stat-icon appointments">
                   <i class="el-icon-check"></i>
                 </div>
                 <div class="stat-content">
                   <span class="stat-label">Completed Today</span>
                   <span class="stat-value">{{ completedToday }}</span>
                 </div>
               </div>
               <div class="stat-item">
                 <div class="stat-icon pending">
                   <i class="el-icon-time"></i>
                 </div>
                 <div class="stat-content">
                   <span class="stat-label">Pending Today</span>
                   <span class="stat-value">{{ pendingToday }}</span>
                 </div>
               </div>
               <!-- <div class="stat-item">
                 <div class="stat-icon revenue">
                   <i class="el-icon-money"></i>
                 </div>
                 <div class="stat-content">
                   <span class="stat-label">Today's Revenue</span>
                   <span class="stat-value">${{ todayRevenue }}</span>
                 </div>
               </div> -->
             </div>
        </div>
         </el-col>
    </el-row>
     </div>

     <!-- Analytics Overview Section -->
     <!-- <div class="analytics-section">
       <el-row :gutter="24">
         <el-col :xs="24" :sm="12" :md="8" :lg="8" :xl="8">
           <div class="analytics-card">
             <div class="analytics-header">
               <h4 class="analytics-title">Patient Growth</h4>
               <i class="el-icon-trend-charts analytics-icon"></i>
             </div>
             <div class="analytics-content">
               <div class="analytics-value">{{ patientGrowthRate }}%</div>
               <div class="analytics-subtitle">vs last month</div>
               <div class="analytics-trend">
                 <i class="el-icon-arrow-up trend-up"></i>
                 <span>+12% this week</span>
               </div>
             </div>
           </div>
      </el-col>
         <el-col :xs="24" :sm="12" :md="8" :lg="8" :xl="8">
           <div class="analytics-card">
             <div class="analytics-header">
               <h4 class="analytics-title">Appointment Rate</h4>
               <i class="el-icon-pie-chart analytics-icon"></i>
             </div>
             <div class="analytics-content">
               <div class="analytics-value">{{ appointmentRate }}%</div>
               <div class="analytics-subtitle">completion rate</div>
               <div class="analytics-trend">
                 <i class="el-icon-arrow-up trend-up"></i>
                 <span>+5% this month</span>
               </div>
             </div>
           </div>
      </el-col>
         <el-col :xs="24" :sm="12" :md="8" :lg="8" :xl="8">
           <div class="analytics-card">
             <div class="analytics-header">
               <h4 class="analytics-title">Revenue Growth</h4>
               <i class="el-icon-coin analytics-icon"></i>
             </div>
             <div class="analytics-content">
               <div class="analytics-value">{{ revenueGrowthRate }}%</div>
               <div class="analytics-subtitle">vs last month</div>
               <div class="analytics-trend">
                 <i class="el-icon-arrow-up trend-up"></i>
                 <span>+8% this week</span>
               </div>
             </div>
           </div>
      </el-col>
       </el-row>
     </div> -->

     <!-- Calendar Section -->
    <div class="calendar-section">
      <div class="calendar-card">
        <div class="calendar-header">
          <div class="calendar-title-section">
            <h3 class="calendar-title">Upcoming Appointments</h3>
            <p class="calendar-subtitle">Manage your daily schedule and appointments</p>
          </div>
          <div class="calendar-actions">
            <el-button-group>
              <el-button size="mini" icon="el-icon-refresh" @click="refreshCalendar">Refresh</el-button>
              <el-button size="mini" icon="el-icon-plus" @click="addAppointment">Add Appointment</el-button>
              <el-button size="mini" @click="viewAllAppointments">View All</el-button>
            </el-button-group>
          </div>
        </div>
        <div class="calendar-content">
          <div class="calendar-wrapper">
            <full-calendar 
              :events="events" 
              locale="en"
              :config="calendarConfig"
              @event-click="handleEventClick"
              @day-click="handleDayClick"
            ></full-calendar>
          </div>
        </div>
       <!--  <div class="calendar-footer" v-if="events && events.length > 0">
          <div class="upcoming-events">
            <h4 class="events-title">Today's Schedule</h4>
            <div class="events-list">
              <div 
                v-for="event in todayEvents" 
                :key="event.id" 
                class="event-item"
                @click="handleEventClick(event)"
              >
                <div class="event-time">
                  <i class="el-icon-time"></i>
                  <span>{{ formatEventTime(event.start) }}</span>
                </div>
                <div class="event-details">
                  <span class="event-title">{{ event.title }}</span>
                  <span class="event-description" v-if="event.description">{{ event.description }}</span>
                </div>
                <div class="event-status">
                  <el-tag 
                    size="mini" 
                    :type="getEventStatusType(event)"
                  >
                    {{ getEventStatus(event) }}
                  </el-tag>
                </div>
              </div>
            </div>
          </div>
        </div> -->
      </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="activity-section" v-if="todayspxs && todayspxs.length > 0">
      <div class="activity-card">
        <div class="activity-header">
          <h3 class="activity-title">Today's Patients</h3>
          <el-button size="mini" @click="viewAllPatients">View All</el-button>
        </div>
        <div class="activity-content">
          <div class="patient-list">
            <div 
              v-for="patient in todayspxs.slice(0, 5)" 
              :key="patient.id" 
              class="patient-item"
            >
              <div class="patient-avatar">
                <i class="el-icon-user"></i>
              </div>
              <div class="patient-info">
                <span class="patient-name">{{ patient.patient || 'Unknown Patient' }}</span>
                <span class="patient-time">{{ patient.appointment_time || 'No time set' }}</span>
              </div>
              <div class="patient-status">
                <el-tag size="mini" type="success">Active</el-tag>
              </div>
            </div>
          </div>
        </div>
      </div>
        </div>
  </div>
</template>

<script>
//import GithubCorner from '@/components/GithubCorner';
import PanelGroup from './components/PanelGroup';
import LineChart from './components/LineChart';
import RaddarChart from './components/RaddarChart';
import PieChart from './components/PieChart';
import BarChart from './components/BarChart';
import TransactionTable from './components/TransactionTable';
import TodoList from './components/TodoList';
import BoxCard from './components/BoxCard';
import fullCalendar from 'vue-fullcalendar'
import Patients from '@/api/patients';
import ApexCharts from "apexcharts";
import VueApexCharts from "vue-apexcharts";

const lineChartData = {
  newVisitis: {
    expectedData: [100, 120, 161, 134, 105, 160, 165],
    actualData: [120, 82, 91, 154, 162, 140, 145],
  },
  messages: {
    expectedData: [200, 192, 120, 144, 160, 130, 140],
    actualData: [180, 160, 151, 106, 145, 150, 130],
  },
  purchases: {
    expectedData: [80, 100, 121, 104, 105, 90, 100],
    actualData: [120, 90, 100, 138, 142, 130, 130],
  },
  shoppings: {
    expectedData: [130, 140, 141, 142, 145, 150, 160],
    actualData: [120, 82, 91, 154, 162, 140, 130],
  },
};

export default {
  name: 'DashboardAdmin',
  components: {
    fullCalendar,
    //GithubCorner,
    PanelGroup,
    LineChart,
    RaddarChart,
    PieChart,
    BarChart,
    TransactionTable,
    TodoList,
    BoxCard,
    apexchart: VueApexCharts,
  },
  created() {
    this.dashoboard();
  },
  data() {
    return {
      lineChartData: lineChartData.newVisitis,
      count_px: 0,
      count_meds: 0,
      count_dx: 0,
      count_appt: 0,
      completed_today: 0,
      pending_today: 0,
      events: [],
      series: [],
      revenue_series: [],
      todayspxs: [],
      calendarConfig: {
        header: {
          left: 'prev,next today',
          center: 'title',
          right: 'month,agendaWeek,agendaDay'
        },
        defaultView: 'month',
        height: 'auto',
        aspectRatio: 1.8,
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        eventLimit: true,
        eventLimitText: 'more',
        eventColor: '#667eea',
        eventTextColor: '#ffffff',
        dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        buttonText: {
          today: 'Today',
          month: 'Month',
          week: 'Week',
          day: 'Day'
        },
        eventRender: this.customEventRender
      },
      chartOptions: {
        chart: {
          height: 350,
          type: "line",
          zoom: {
            enabled: false,
          },
        },
        dataLabels: {
          enabled: true,
          background: {
            enabled: true,
            foreColor: "#000000",
            padding: 4,
            borderRadius: 2,
            borderWidth: 1,
            borderColor: "#000000",
            opacity: 0.9,
            dropShadow: {
              enabled: false,
              top: 1,
              left: 1,
              blur: 1,
              color: "#000000",
              opacity: 0.45,
            },
          },
        },
        stroke: {
          curve: "straight",
        },
        title: {
          text: "Monthly Census Report",
          align: "left",
        },
        grid: {
          row: {
            colors: ["#f3f3f3", "transparent"],
            opacity: 0.5,
          },
        },
        xaxis: {
          categories: [],
        },
        legend: {
          position: "top",
          horizontalAlign: "right",
          floating: true,
          offsetY: -25,
          offsetX: -5,
        },
      },
      chartOptionsBar: {
        chart: {
          height: 350,
          type: "line",
          zoom: {
            enabled: false,
          },
        },
        dataLabels: {
          enabled: true,
          background: {
            enabled: true,
            foreColor: "#000000",
            padding: 4,
            borderRadius: 2,
            borderWidth: 1,
            borderColor: "#000000",
            opacity: 0.9,
            dropShadow: {
              enabled: false,
              top: 1,
              left: 1,
              blur: 1,
              color: "#000000",
              opacity: 0.45,
            },
          },
        },
        stroke: {
          curve: "straight",
        },
        title: {
          text: "Monthly Revenue Report",
          align: "left",
        },
        grid: {
          row: {
            colors: ["#f3f3f3", "transparent"],
            opacity: 0.5,
          },
        },
        xaxis: {
          categories: [],
        },
        legend: {
          position: "top",
          horizontalAlign: "right",
          floating: true,
          offsetY: -25,
          offsetX: -5,
        },
      },
    };
  },
  computed: {
    todayEvents() {
      if (!this.events || this.events.length === 0) return [];
      
      const today = new Date();
      const todayStr = today.toISOString().split('T')[0];
      
      return this.events.filter(event => {
        const eventDate = new Date(event.start);
        const eventDateStr = eventDate.toISOString().split('T')[0];
        return eventDateStr === todayStr;
      }).sort((a, b) => new Date(a.start) - new Date(b.start));
    },
    completedToday() {
      // Get completed count from API response
      return this.completed_today || 0;
    },
    pendingToday() {
      // Get pending count from API response
      return this.pending_today || 0;
    },
    todayRevenue() {
      // Calculate today's revenue (mock calculation - replace with actual data)
      const baseRevenue = this.completedToday * 150; // Assuming $150 per completed appointment
      return baseRevenue.toLocaleString();
    },
    patientGrowthRate() {
      // Mock calculation for patient growth rate
      return Math.floor(Math.random() * 20) + 10; // Random between 10-30%
    },
    appointmentRate() {
      // Calculate appointment completion rate
      const totalToday = this.completedToday + this.pendingToday;
      if (totalToday === 0) return 0;
      return Math.round((this.completedToday / totalToday) * 100);
    },
    revenueGrowthRate() {
      // Mock calculation for revenue growth rate
      return Math.floor(Math.random() * 15) + 5; // Random between 5-20%
    }
  },
  methods: {
    handleSetLineChartData(type) {
      this.lineChartData = lineChartData[type];
    },
    dashoboard() {
      Patients.dashboard().then((response) => {
        this.count_px = response.patients;
        this.count_meds = response.meds;
        this.count_dx = response.dx;
        this.count_appt = response.appt;
        this.completed_today = response.completed_today || 0;
        this.pending_today = response.pending_today || 0;
        this.series = response.graph_census;
        this.revenue_series = response.graph_amt;
        this.chartOptions = {
          xaxis: {
            categories: response.data_graph_month,
          },
        };
        this.chartOptionsBar = {
          xaxis: {
            categories: response.revenue_mon,
          },
        };
        this.events = response.calendar;
        console.log(this.events)
        this.todayspxs = response.todaysAppt;
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    handleQuickAction() {
      this.$message.info('Quick action functionality will be implemented');
    },
    refreshChart() {
      this.dashoboard();
      this.$message.success('Chart refreshed successfully');
    },
    exportChart() {
      this.$message.info('Export functionality will be implemented');
    },
    viewAllAppointments() {
      this.$router.push('/appointments');
    },
    viewAllPatients() {
      this.$router.push('/patients');
    },
    refreshCalendar() {
      this.dashoboard();
      this.$message.success('Calendar refreshed successfully');
    },
    addAppointment() {
      this.$router.push('/appointments/form');
    },
    handleEventClick(event) {
      this.$message.info(`Event clicked: ${event.title}`);
      // You can add more functionality here like opening a modal with event details
    },
    handleDayClick(date) {
      this.$message.info(`Day clicked: ${date.format()}`);
      // You can add functionality to create new appointments on day click
    },
    formatEventTime(startTime) {
      const date = new Date(startTime);
      return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
      });
    },
    getEventStatus(event) {
      const now = new Date();
      const eventTime = new Date(event.start);
      
      if (eventTime < now) {
        return 'Completed';
      } else if (eventTime.toDateString() === now.toDateString()) {
        return 'Today';
      } else {
        return 'Upcoming';
      }
    },
    getEventStatusType(event) {
      const now = new Date();
      const eventTime = new Date(event.start);
      
      if (eventTime < now) {
        return 'success';
      } else if (eventTime.toDateString() === now.toDateString()) {
        return 'warning';
      } else {
        return 'info';
      }
    },
    customEventRender(event, element) {
      // Custom event rendering for better visual appearance
      element.css({
        'border-radius': '8px',
        'border': 'none',
        'box-shadow': '0 2px 8px rgba(0,0,0,0.1)',
        'font-weight': '500'
        });
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 24px;
  
  .dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
  padding: 32px;
    margin-bottom: 32px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    
    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 20px;
      
      .welcome-section {
        .dashboard-title {
          font-size: 2.5rem;
          font-weight: 700;
          margin: 0 0 8px 0;
          text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-subtitle {
          font-size: 1.1rem;
          opacity: 0.9;
          margin: 0;
          font-weight: 300;
        }
      }
      
      .header-actions {
        .el-button {
          background: rgba(255, 255, 255, 0.2);
          border: 2px solid rgba(255, 255, 255, 0.3);
          color: white;
          font-weight: 600;
          padding: 12px 24px;
          border-radius: 12px;
          transition: all 0.3s ease;
          
          &:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
          }
        }
      }
    }
  }
  
  .stats-section {
    margin-bottom: 32px;
  }
  
  .charts-section {
    margin-bottom: 32px;
    
    .chart-card {
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      
      &:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
      }
      
      .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f2f5;
        
        .chart-title {
          font-size: 1.5rem;
          font-weight: 600;
          color: #2c3e50;
          margin: 0;
        }
        
        .chart-actions {
          .el-button-group {
            .el-button {
              border-radius: 8px;
              font-weight: 500;
              transition: all 0.3s ease;
              
              &:hover {
                transform: translateY(-1px);
              }
            }
          }
        }
      }
      
      .chart-content {
        position: relative;
      }
    }
    
    .quick-stats-card {
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(0, 0, 0, 0.05);
      height: 100%;
      transition: all 0.3s ease;
      
      &:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
      }
      
      .card-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0 0 24px 0;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f2f5;
      }
      
      .quick-stats {
        .stat-item {
          display: flex;
          align-items: center;
          padding: 16px 0;
          border-bottom: 1px solid #f8f9fa;
          
          &:last-child {
            border-bottom: none;
          }
          
          .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 20px;
            color: white;
            
            &.patients {
              background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            
             &.appointments {
               background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
             }
            
             &.medicines {
               background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
             }
             
             &.pending {
               background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
             }
             
             &.revenue {
               background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
             }
          }
          
          .stat-content {
            flex: 1;
            
            .stat-label {
              display: block;
              font-size: 0.9rem;
              color: #6c757d;
              margin-bottom: 4px;
              font-weight: 500;
            }
            
            .stat-value {
              display: block;
              font-size: 1.5rem;
              font-weight: 700;
              color: #2c3e50;
            }
          }
        }
      }
    }
   }
   
   .analytics-section {
     margin-bottom: 32px;
     
     .analytics-card {
       background: white;
       border-radius: 16px;
       padding: 24px;
       box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
       border: 1px solid rgba(0, 0, 0, 0.05);
       transition: all 0.3s ease;
       height: 100%;
       
       &:hover {
         transform: translateY(-4px);
         box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
       }
       
       .analytics-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 20px;
         
         .analytics-title {
           font-size: 1.1rem;
           font-weight: 600;
           color: #2c3e50;
           margin: 0;
         }
         
         .analytics-icon {
           font-size: 24px;
           color: #667eea;
           opacity: 0.8;
         }
       }
       
       .analytics-content {
         .analytics-value {
           font-size: 2.5rem;
           font-weight: 700;
           color: #2c3e50;
           margin-bottom: 8px;
           background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
           -webkit-background-clip: text;
           -webkit-text-fill-color: transparent;
           background-clip: text;
         }
         
         .analytics-subtitle {
           font-size: 0.9rem;
           color: #6c757d;
           margin-bottom: 12px;
           font-weight: 500;
         }
         
         .analytics-trend {
           display: flex;
           align-items: center;
           font-size: 0.85rem;
           color: #28a745;
           font-weight: 600;
           
           .trend-up {
             margin-right: 6px;
             font-size: 14px;
           }
         }
       }
     }
   }
   
   .calendar-section {
    margin-bottom: 32px;
    
    .calendar-card {
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      
      &:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
      }
      
      .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f2f5;
        
        .calendar-title-section {
          .calendar-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 4px 0;
          }
          
          .calendar-subtitle {
            font-size: 0.9rem;
            color: #6c757d;
            margin: 0;
            font-weight: 400;
          }
        }
        
        .calendar-actions {
          .el-button-group {
            .el-button {
              border-radius: 8px;
              font-weight: 500;
              transition: all 0.3s ease;
              margin-left: 4px;
              
              &:first-child {
                margin-left: 0;
              }
              
              &:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
              }
            }
          }
        }
      }
      
      .calendar-content {
        .calendar-wrapper {
          background: #fafbfc;
          border-radius: 12px;
          padding: 16px;
          border: 1px solid #e9ecef;

.comp-full-calendar {
    max-width: 100%;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            
            // Custom FullCalendar styles
            .fc-toolbar {
              background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
              color: white;
              padding: 16px 20px;
              border-radius: 8px 8px 0 0;
              margin-bottom: 0;
              
              .fc-button {
                background: rgba(255, 255, 255, 0.2);
                border: 1px solid rgba(255, 255, 255, 0.3);
                color: white;
                border-radius: 6px;
                font-weight: 500;
                transition: all 0.3s ease;
                
                &:hover {
                  background: rgba(255, 255, 255, 0.3);
                  border-color: rgba(255, 255, 255, 0.5);
                  transform: translateY(-1px);
                }
                
                &.fc-state-active {
                  background: rgba(255, 255, 255, 0.4);
                  border-color: rgba(255, 255, 255, 0.6);
                }
              }
              
              .fc-toolbar-title {
                font-size: 1.3rem;
                font-weight: 600;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
              }
            }
            
            .fc-view-container {
              border-radius: 0 0 8px 8px;
              overflow: hidden;
            }
            
            .fc-day-header {
              background: #f8f9fa;
              color: #495057;
              font-weight: 600;
              padding: 12px 8px;
              border-color: #dee2e6;
            }
            
            .fc-day {
              border-color: #dee2e6;
              transition: all 0.3s ease;
              
              &:hover {
                background: #f8f9fa;
              }
              
              &.fc-today {
                background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
                border-color: #667eea;
              }
            }
            
            .fc-event {
              border-radius: 6px;
              border: none;
              padding: 4px 8px;
              font-weight: 500;
              box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
              transition: all 0.3s ease;
              
              &:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
              }
            }
          }
        }
      }
      
      .calendar-footer {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 2px solid #f0f2f5;
        
        .upcoming-events {
          .events-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 16px 0;
            display: flex;
            align-items: center;
            
            &::before {
              content: '📅';
              margin-right: 8px;
              font-size: 1.1rem;
            }
          }
          
          .events-list {
            .event-item {
              display: flex;
              align-items: center;
              padding: 12px 16px;
              margin-bottom: 8px;
              background: #f8f9fa;
              border-radius: 10px;
              border-left: 4px solid #667eea;
              cursor: pointer;
              transition: all 0.3s ease;
              
              &:hover {
                background: #e9ecef;
                transform: translateX(4px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
              }
              
              &:last-child {
                margin-bottom: 0;
              }
              
              .event-time {
                display: flex;
                align-items: center;
                margin-right: 16px;
                color: #667eea;
                font-weight: 600;
                font-size: 0.9rem;
                
                i {
                  margin-right: 6px;
                  font-size: 14px;
                }
              }
              
              .event-details {
                flex: 1;
                
                .event-title {
                  display: block;
                  font-weight: 600;
                  color: #2c3e50;
                  margin-bottom: 2px;
                  font-size: 0.95rem;
                }
                
                .event-description {
                  display: block;
                  font-size: 0.85rem;
                  color: #6c757d;
                }
              }
              
              .event-status {
                .el-tag {
                  border-radius: 20px;
                  font-weight: 500;
                  font-size: 0.75rem;
                }
              }
            }
          }
        }
      }
    }
  }
  
  .activity-section {
    .activity-card {
      background: white;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      
      &:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
      }
      
      .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f2f5;
        
        .activity-title {
          font-size: 1.5rem;
          font-weight: 600;
          color: #2c3e50;
          margin: 0;
        }
        
        .el-button {
          border-radius: 8px;
          font-weight: 500;
          transition: all 0.3s ease;
          
          &:hover {
            transform: translateY(-1px);
          }
        }
      }
      
      .activity-content {
        .patient-list {
          .patient-item {
            display: flex;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f8f9fa;
            transition: all 0.3s ease;
            
            &:last-child {
              border-bottom: none;
            }
            
            &:hover {
              background: #f8f9fa;
              border-radius: 8px;
              padding: 16px 12px;
              margin: 0 -12px;
            }
            
            .patient-avatar {
              width: 40px;
              height: 40px;
              border-radius: 50%;
              background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
              display: flex;
              align-items: center;
              justify-content: center;
              margin-right: 16px;
              color: white;
              font-size: 18px;
            }
            
            .patient-info {
              flex: 1;
              
              .patient-name {
                display: block;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 4px;
              }
              
              .patient-time {
                display: block;
                font-size: 0.9rem;
                color: #6c757d;
              }
            }
            
            .patient-status {
              .el-tag {
                border-radius: 20px;
                font-weight: 500;
              }
            }
          }
        }
      }
    }
  }
}

// Responsive Design
@media (max-width: 768px) {
  .dashboard-container {
    padding: 16px;
    
    .dashboard-header {
      padding: 24px 20px;
      
      .header-content {
        flex-direction: column;
        text-align: center;
        
        .welcome-section {
          .dashboard-title {
            font-size: 2rem;
          }
          
          .dashboard-subtitle {
            font-size: 1rem;
          }
        }
      }
    }
    
    .charts-section {
      .chart-card,
      .quick-stats-card {
        margin-bottom: 20px;
      }
    }
  }
}

// Animation keyframes
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dashboard-container > * {
  animation: fadeInUp 0.6s ease-out;
}

.is-selected {
  color: #1989fa;
}
</style>
