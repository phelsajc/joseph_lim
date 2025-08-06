<template>
  <div class="dashboard-editor-container">
    <!-- <github-corner style="position: absolute; top: 0px; border: 0; right: 0;" /> -->

    <panel-group @handleSetLineChartData="handleSetLineChartData" :total_patients="count_px" :total_meds="count_meds" :total_dxs="count_dx" :total_appts="count_appt"/>

    <el-row style="background:#fff;padding:16px 16px 0;margin-bottom:32px;">
      <!-- <line-chart :chart-data="lineChartData" /> -->
      <div id="chart">
              <apexchart
                ref="radar"
                type="line"
                height="350"
                :options="chartOptions"
                :series="series"
              ></apexchart>
            </div>
    </el-row>

    <el-row style="background:#fff;padding:16px 16px 0;margin-bottom:32px;">
      <full-calendar :events="events" locale="en"></full-calendar>
    </el-row>

    <el-row :gutter="32">
      <el-col :xs="{span: 24}" :sm="{span: 24}" :md="{span: 24}" :lg="{span: 12}" :xl="{span: 12}" style="padding-right:8px;margin-bottom:30px;">
        <!-- <transaction-table :patients="todayspxs" /> -->
      </el-col>
      <!-- <el-col :xs="24" :sm="24" :lg="12">
        <div class="chart-wrapper">
          <apexchart
            ref="radar"
            type="bar"
            height="350"
            :options="chartOptionsBar"
            :series="revenue_series"
          ></apexchart>
        </div>
      </el-col> -->
    </el-row>

    <!-- <el-row :gutter="8">
      <el-col :xs="{span: 24}" :sm="{span: 24}" :md="{span: 24}" :lg="{span: 12}" :xl="{span: 12}" style="padding-right:8px;margin-bottom:30px;">
        <transaction-table />
      </el-col>
      <el-col :xs="{span: 24}" :sm="{span: 12}" :md="{span: 12}" :lg="{span: 6}" :xl="{span: 6}" style="margin-bottom:30px;">
        <todo-list />
      </el-col>
      <el-col :xs="{span: 24}" :sm="{span: 12}" :md="{span: 12}" :lg="{span: 6}" :xl="{span: 6}" style="margin-bottom:30px;">
        <box-card />
      </el-col>
    </el-row> -->
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
      events: [],
      series: [],
      revenue_series: [],
      todayspxs: [],
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
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard-editor-container {
  padding: 32px;
  background-color: rgb(240, 242, 245);
  .chart-wrapper {
    background: #fff;
    padding: 16px 16px 0;
    margin-bottom: 32px;
  }
}
.is-selected {
  color: #1989fa;
}

.comp-full-calendar {
    max-width: 100%;
}

</style>
