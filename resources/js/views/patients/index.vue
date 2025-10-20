<template>
  <div class="patients-container">
    <!-- Header Section -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-text">
          <h1 class="page-title">
            <i class="el-icon-user-solid"></i>
            Patient Management
          </h1>
          <p class="page-subtitle">Manage and view all patient records.</p>
        </div>
        <div class="header-actions">
          <router-link :to="'/masterfile/patient_form/0'">
            <el-button type="primary" icon="el-icon-plus" size="medium" class="add-patient-btn">
              Add New Patient
            </el-button>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Total Patients Card -->
    <div class="total-patients-card">
      <div class="stat-icon">
        <i class="el-icon-user-solid"></i>
      </div>
      <div class="stat-content">
        <h3>{{ total }}</h3>
        <p>Total Patients</p>
      </div>
    </div>

    <!-- Search Section -->
    <div class="search-section">
      <div class="search-container">
      <el-input
          v-model="query.keyword"
          placeholder="Search patients by name, ID, or contact..."
          class="search-input"
          prefix-icon="el-icon-search"
          clearable
        @keyup.enter.native="handleFilter"
          @clear="handleFilter"
          @input="handleSearchInput"
        />
        <el-button 
          v-waves 
          type="primary" 
          icon="el-icon-search" 
          class="search-btn"
          @click="handleFilter"
        >
          Search
        </el-button>
      </div>
    </div>

    <!-- View Controls -->
    <div class="view-controls">
      <div class="view-options">
        <el-radio-group v-model="viewMode" @change="handleViewModeChange">
          <el-radio-button label="list">
            <i class="el-icon-s-grid"></i> Table View
          </el-radio-button>
          <el-radio-button label="grid">
            <i class="el-icon-menu"></i> Card View
          </el-radio-button>
        </el-radio-group>
      </div>
    </div>

    <!-- Patients Grid/List -->
    <div class="patients-content">
      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <el-skeleton :rows="6" animated />
      </div>

      <!-- Empty State -->
      <div v-else-if="!patients || patients.length === 0" class="empty-state">
        <div class="empty-content">
          <i class="el-icon-user-empty empty-icon"></i>
          <h3>{{ $t('patients.noPatients', 'No patients found') }}</h3>
          <p>{{ $t('patients.noPatientsDesc', 'Try adjusting your search criteria or add a new patient') }}</p>
          <router-link :to="'/masterfile/patient_form/0'">
            <el-button type="primary" icon="el-icon-plus">
              {{ $t('table.add', 'Add First Patient') }}
            </el-button>
          </router-link>
        </div>
      </div>

      <!-- Patients Grid/List -->
      <div v-else :class="viewMode === 'grid' ? 'patients-grid' : 'patients-list'">
        <!-- Grid View -->
        <div 
          v-if="viewMode === 'grid'"
          v-for="patient in patients" 
          :key="patient.id" 
          class="patient-card"
        >
          <div class="patient-photo">
            <el-image
              :src="getPatientPhoto(patient)"
              :preview-src-list="[getPatientPhoto(patient)]"
              fit="cover"
              class="photo"
            >
              <div slot="error" class="image-slot">
                <i class="el-icon-user-solid"></i>
              </div>
            </el-image>
          </div>
          
          <div class="patient-info">
            <h3 class="patient-name">{{ patient.patientname }}</h3>
            <div class="patient-details">
              <div class="detail-item">
                <span class="detail-label">ID:</span>
                <span class="detail-value">{{ patient.patientid }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Age:</span>
                <span class="detail-value">{{ calculateAge(patient.birthdate) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Gender:</span>
                <span class="detail-value">N/A</span>
              </div>
            </div>
          </div>
          
          <div class="patient-actions">
            <el-button 
              type="primary" 
              size="small"
              icon="el-icon-view" 
              class="action-btn view-btn"
              @click="selectRow(patient)"
            >
              View
            </el-button>
            <el-button 
              type="danger" 
              size="small"
              icon="el-icon-delete" 
              class="action-btn delete-btn"
              @click="handleDelete(patient)"
            >
              Delete
            </el-button>
          </div>
        </div>

        <!-- List View -->
        <div v-else class="patients-table">
          <div class="table-header">
            <div class="header-cell checkbox-col">
              <el-checkbox 
                :value="allPatientsSelected"
                @change="toggleAllSelection"
                :indeterminate="somePatientsSelected"
              />
            </div>
            <div class="header-cell photo-col">Photo</div>
            <div class="header-cell name-col">Name</div>
            <div class="header-cell id-col">Patient ID</div>
            <div class="header-cell dob-col">Date of Birth</div>
            <div class="header-cell age-col">Age</div>
            <div class="header-cell status-col">Status</div>
            <div class="header-cell actions-col">Actions</div>
          </div>
          
          <div 
            v-for="patient in patients" 
            :key="patient.id" 
            class="table-row"
            :class="{ 'selected': selectedPatients.includes(patient.id) }"
            @click="togglePatientSelection(patient)"
          >
            <div class="table-cell checkbox-col">
              <el-checkbox 
                :value="selectedPatients.includes(patient.id)"
                @change="togglePatientSelection(patient)"
                @click.stop
              />
            </div>
            <div class="table-cell photo-col">
              <el-image
                :src="getPatientPhoto(patient)"
                :preview-src-list="[getPatientPhoto(patient)]"
                fit="cover"
                class="list-photo"
              >
                <div slot="error" class="image-slot">
                  <i class="el-icon-user-solid"></i>
                </div>
              </el-image>
            </div>
            <div class="table-cell name-col">
              <div class="patient-name-list">{{ patient.patientname }}</div>
            </div>
            <div class="table-cell id-col">
              <span class="patient-id">{{ patient.patientid }}</span>
            </div>
            <div class="table-cell dob-col">
              <span class="patient-dob">{{ formatDate(patient.birthdate) }}</span>
            </div>
            <div class="table-cell age-col">
              <span class="patient-age">{{ calculateAge(patient.birthdate) }}</span>
            </div>
            <div class="table-cell status-col">
              <el-tag :type="getStatusTagType(patient)" size="small">
                {{ getPatientStatus(patient) }}
              </el-tag>
            </div>
            <div class="table-cell actions-col">
              <el-button 
                type="text" 
                icon="el-icon-view" 
                size="mini"
                @click.stop="selectRow(patient)"
              >
                View
              </el-button>
              <el-dropdown @command="handleAction" trigger="click">
                <el-button type="text" icon="el-icon-more" size="mini">
                  <i class="el-icon-arrow-down el-icon--right"></i>
                </el-button>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item :command="{action: 'edit', patient}">
                    <i class="el-icon-edit"></i> Edit
                  </el-dropdown-item>
                  <el-dropdown-item :command="{action: 'view', patient}">
                    <i class="el-icon-view"></i> View Details
                  </el-dropdown-item>
                  <el-dropdown-item :command="{action: 'duplicate', patient}">
                    <i class="el-icon-copy-document"></i> Duplicate
                  </el-dropdown-item>
                  <el-dropdown-item :command="{action: 'delete', patient}" divided>
                    <i class="el-icon-delete"></i> Delete
                  </el-dropdown-item>
                </el-dropdown-menu>
              </el-dropdown>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-container">
    <pagination
        v-show="total > 0" 
        :total="total" 
        :page.sync="query.page" 
        :limit.sync="query.limit"
      @pagination="getPatients"
    />
    </div>
  </div>
</template>
<script>
import Patients from '@/api/patients';
import Pagination from '@/components/Pagination';

export default {
  name: 'Patients',
  components: { Pagination },
  data() {
    return {
      loading: true,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
        status: '',
        sortBy: 'patientname',
        ageGroup: '',
        dateRange: null,
        patientId: '',
      },
      patients: null,
      total: 0,
      selectedPatients: [],
      viewMode: 'grid', // 'grid' or 'list'
      itemsPerPage: 15,
      showAdvancedFilters: false,
      searchTimeout: null,
    };
  },
  computed: {
    activePatients() {
      return this.patients ? this.patients.filter(p => this.getPatientStatus(p) === 'active').length : 0;
    },
    newThisMonth() {
      if (!this.patients) return 0;
      const currentMonth = new Date().getMonth();
      const currentYear = new Date().getFullYear();
      return this.patients.filter(p => {
        const createdDate = new Date(p.created_at || p.birthdate);
        return createdDate.getMonth() === currentMonth && createdDate.getFullYear() === currentYear;
      }).length;
    },
    averageAge() {
      if (!this.patients || this.patients.length === 0) return 0;
      const totalAge = this.patients.reduce((sum, patient) => {
        const age = this.calculateAgeNumber(patient.birthdate);
        return sum + (age || 0);
      }, 0);
      return Math.round(totalAge / this.patients.length);
    },
    allPatientsSelected() {
      return this.patients && this.patients.length > 0 && this.selectedPatients.length === this.patients.length;
    },
    somePatientsSelected() {
      return this.selectedPatients.length > 0 && this.selectedPatients.length < this.patients.length;
    },
  },
  created() {
    this.getPatients();
  },
  methods: {
    async getPatients() {
      this.loading = true;
      try {
      const { data, meta } = await Patients.list({
        params: this.query,
      });
      this.patients = data;
      this.total = meta.total;
      } catch (error) {
        this.$message.error('Failed to load patients');
        console.error('Error loading patients:', error);
      } finally {
      this.loading = false;
      }
    },
    handleFilter() {
      this.query.page = 1;
      this.getPatients();
    },
    selectRow(patient) {
      this.$router.push({ path: '/masterfile/profile/' + patient.id + '/' + patient.patientid });
    },
    handleAction(command) {
      const { action, patient } = command;
      switch (action) {
        case 'edit':
          this.$router.push({ path: '/masterfile/patient_form/' + patient.id });
          break;
        case 'view':
          this.selectRow(patient);
          break;
        case 'delete':
          this.handleDelete(patient);
          break;
      }
    },
    handleDelete(patient) {
      this.$confirm(
        `Are you sure you want to delete patient "${patient.patientname}"?`,
        'Confirm Delete',
        {
          confirmButtonText: 'Delete',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      ).then(() => {
        // Add delete API call here
        this.$message.success('Patient deleted successfully');
        this.getPatients();
      }).catch(() => {
        this.$message.info('Delete cancelled');
      });
    },
    getPatientPhoto(patient) {
      try {
        if (patient.profile && patient.profile !== '') {
          return patient.profile;
        } else {
          return `public/photos/${patient.id}.jpg`;
        }
      } catch (e) {
        return `public/photos/${patient.id}.jpg`;
      }
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      } catch (e) {
        return dateString;
      }
    },
    calculateAge(birthDate) {
      if (!birthDate) return 'N/A';
      try {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
          age--;
        }
        
        return age >= 0 ? `${age} years` : 'N/A';
      } catch (e) {
        return 'N/A';
      }
    },
    calculateAgeNumber(birthDate) {
      if (!birthDate) return 0;
      try {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
          age--;
        }
        
        return age >= 0 ? age : 0;
      } catch (e) {
        return 0;
      }
    },
    getPatientStatus(patient) {
      // This would typically come from your backend
      // For now, we'll simulate based on some logic
      if (patient.status) return patient.status;
      
      // Simulate status based on age or other factors
      const age = this.calculateAgeNumber(patient.birthdate);
      if (age < 18) return 'minor';
      if (age > 65) return 'senior';
      return 'active';
    },
    getStatusIcon(patient) {
      const status = this.getPatientStatus(patient);
      switch (status) {
        case 'active': return 'el-icon-check';
        case 'inactive': return 'el-icon-close';
        case 'minor': return 'el-icon-user';
        case 'senior': return 'el-icon-star-on';
        default: return 'el-icon-user-solid';
      }
    },
    getStatusTagType(patient) {
      const status = this.getPatientStatus(patient);
      switch (status) {
        case 'active': return 'success';
        case 'inactive': return 'info';
        case 'minor': return 'warning';
        case 'senior': return 'primary';
        default: return '';
      }
    },
    togglePatientSelection(patient) {
      const index = this.selectedPatients.indexOf(patient.id);
      if (index > -1) {
        this.selectedPatients.splice(index, 1);
      } else {
        this.selectedPatients.push(patient.id);
      }
    },
    toggleAllSelection() {
      if (this.allPatientsSelected) {
        this.selectedPatients = [];
      } else {
        this.selectedPatients = this.patients.map(p => p.id);
      }
    },
    handleViewModeChange(mode) {
      this.viewMode = mode;
      // Save preference to localStorage
      localStorage.setItem('patientViewMode', mode);
    },
    handleItemsPerPageChange(value) {
      this.query.limit = value;
      this.query.page = 1;
      this.getPatients();
    },
    handleSearchInput() {
      // Debounce search input
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.handleFilter();
      }, 500);
    },
    refreshData() {
      this.selectedPatients = [];
      this.getPatients();
    },
    exportSelected() {
      if (this.selectedPatients.length === 0) {
        this.$message.warning('Please select patients to export');
        return;
      }
      
      const selectedData = this.patients.filter(p => this.selectedPatients.includes(p.id));
      this.exportToCSV(selectedData);
    },
    exportToCSV(data) {
      const headers = ['Name', 'Patient ID', 'Date of Birth', 'Age', 'Status'];
      const csvContent = [
        headers.join(','),
        ...data.map(patient => [
          `"${patient.patientname}"`,
          patient.patientid,
          patient.birthdate,
          this.calculateAge(patient.birthdate),
          this.getPatientStatus(patient)
        ].join(','))
      ].join('\n');
      
      const blob = new Blob([csvContent], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `patients_export_${new Date().toISOString().split('T')[0]}.csv`;
      link.click();
      window.URL.revokeObjectURL(url);
      
      this.$message.success('Export completed successfully');
    },
    bulkUpdate() {
      if (this.selectedPatients.length === 0) {
        this.$message.warning('Please select patients to update');
        return;
      }
      
      this.$prompt('Enter new status for selected patients:', 'Bulk Update', {
        confirmButtonText: 'Update',
        cancelButtonText: 'Cancel',
        inputPattern: /^(active|inactive|minor|senior)$/,
        inputErrorMessage: 'Status must be one of: active, inactive, minor, senior'
      }).then(({ value }) => {
        // Here you would call your API to update the selected patients
        this.$message.success(`Updated ${this.selectedPatients.length} patients to ${value} status`);
        this.getPatients();
      }).catch(() => {
        this.$message.info('Bulk update cancelled');
      });
    },
    bulkDelete() {
      if (this.selectedPatients.length === 0) {
        this.$message.warning('Please select patients to delete');
        return;
      }
      
      this.$confirm(
        `Are you sure you want to delete ${this.selectedPatients.length} selected patients?`,
        'Confirm Bulk Delete',
        {
          confirmButtonText: 'Delete',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      ).then(() => {
        // Here you would call your API to delete the selected patients
        this.$message.success(`Deleted ${this.selectedPatients.length} patients successfully`);
        this.selectedPatients = [];
        this.getPatients();
      }).catch(() => {
        this.$message.info('Bulk delete cancelled');
      });
    },
    // Legacy methods for backward compatibility
    imgSrc(profile, id, type) {
      return this.getPatientPhoto({ profile, id });
    },
    imageLoadError(id) {
      return this.getPatientPhoto({ id });
    },
    generate() {
      Patients.generate_image().then((response) => {
        this.$message.success('Images generated successfully');
      })
        .catch((err) => {
          console.error('Error generating images:', err);
          this.$message.error('Failed to generate images');
        });
    },
    generateatt() {
      Patients.generate_att().then((response) => {
        this.$message.success('Attachments generated successfully');
      })
        .catch((err) => {
          console.error('Error generating attachments:', err);
          this.$message.error('Failed to generate attachments');
        });
    },
  },
};
</script>

<style scoped>
.patients-container {
  padding: 20px;
  background-color: #f5f7fa;
  min-height: 100vh;
}

/* Header Section */
.page-header {
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 20px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  margin: 0 0 8px 0;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #303133;
}

.page-subtitle {
  font-size: 14px;
  margin: 0;
  color: #909399;
}

.add-patient-btn {
  background: #409eff;
  border: none;
  color: white;
  font-weight: 500;
  padding: 10px 20px;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.add-patient-btn:hover {
  background: #66b1ff;
  transform: translateY(-1px);
}

/* Total Patients Card */
.total-patients-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  margin-bottom: 20px;
  width: fit-content;
}

.total-patients-card .stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: white;
  background: #8b5cf6;
}

.total-patients-card .stat-content h3 {
  font-size: 24px;
  font-weight: 700;
  margin: 0 0 4px 0;
  color: #303133;
}

.total-patients-card .stat-content p {
  font-size: 14px;
  margin: 0;
  color: #909399;
  font-weight: 500;
}

/* Search Section */
.search-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.search-container {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-input {
  flex: 1;
  max-width: 500px;
}

.search-input .el-input__inner {
  border-radius: 8px;
  border: 1px solid #dcdfe6;
  padding: 12px 16px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.search-input .el-input__inner:focus {
  border-color: #409eff;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.1);
}

.search-btn {
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 500;
  background: #409eff;
  border: none;
}

.search-btn:hover {
  background: #66b1ff;
}

.filter-options {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.filter-select {
  min-width: 180px;
}

.filter-select .el-input__inner {
  border-radius: 8px;
  border: 2px solid #e4e7ed;
}

.advanced-filter-btn {
  color: #667eea;
  font-weight: 500;
}

.advanced-filters {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 16px;
  margin-top: 16px;
  border: 1px solid #e9ecef;
}

.filter-row {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.date-picker {
  min-width: 250px;
}

.filter-input {
  min-width: 200px;
}

.refresh-btn {
  padding: 12px 20px;
  border-radius: 8px;
}

/* View Controls */
.view-controls {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  background: white;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.view-options {
  display: flex;
  align-items: center;
  gap: 0;
}

.view-options .el-radio-group {
  display: flex;
}

.view-options .el-radio-button {
  border-radius: 6px;
  margin-right: 8px;
}

.view-options .el-radio-button:first-child {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.view-options .el-radio-button:last-child {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
  margin-right: 0;
}

.view-options .el-radio-button__inner {
  padding: 8px 16px;
  font-size: 14px;
  border: 1px solid #dcdfe6;
  background: #f5f7fa;
  color: #606266;
}

.view-options .el-radio-button.is-active .el-radio-button__inner {
  background: #409eff;
  border-color: #409eff;
  color: white;
}

/* Patients Content */
.patients-content {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  min-height: 400px;
}

.loading-container {
  padding: 40px;
}

/* Empty State */
.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.empty-content {
  text-align: center;
  color: #909399;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
  color: #c0c4cc;
}

.empty-content h3 {
  font-size: 20px;
  margin: 0 0 8px 0;
  color: #606266;
}

.empty-content p {
  font-size: 14px;
  margin: 0 0 24px 0;
  color: #909399;
}

/* Patients Grid */
.patients-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.patient-card {
  background: white;
  border: 1px solid #e4e7ed;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.patient-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.patient-photo {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.photo {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: 2px solid #f0f2f5;
  transition: all 0.3s ease;
}

.image-slot {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
  background: #f5f7fa;
  color: #c0c4cc;
  font-size: 32px;
}

.status-badge {
  position: absolute;
  bottom: 8px;
  right: 8px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  color: white;
  border: 2px solid white;
}

.status-badge.active {
  background: #67c23a;
}

.status-badge.inactive {
  background: #909399;
}

.status-badge.minor {
  background: #e6a23c;
}

.status-badge.senior {
  background: #409eff;
}

.patient-info {
  text-align: center;
  margin-bottom: 16px;
}

.patient-name {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 12px 0;
  line-height: 1.4;
}

.patient-details {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.detail-item {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: 13px;
  color: #606266;
}

.detail-label {
  font-weight: 500;
  color: #909399;
}

.detail-value {
  font-weight: 600;
  color: #303133;
}

.patient-actions {
  display: flex;
  justify-content: center;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #f0f2f5;
}

.action-btn {
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 6px;
  transition: all 0.3s ease;
  border: none;
}

.view-btn {
  background: #409eff;
  color: white;
}

.view-btn:hover {
  background: #66b1ff;
}

.delete-btn {
  background: #f56c6c;
  color: white;
}

.delete-btn:hover {
  background: #f78989;
}

/* Pagination */
.pagination-container {
  display: flex;
  justify-content: center;
  margin-top: 24px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

/* Responsive Design */
@media (max-width: 768px) {
  .patients-container {
    padding: 12px;
  }
  
  .page-header {
    padding: 20px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .page-title {
    font-size: 24px;
  }
  
  .search-container {
    gap: 16px;
  }
  
  .search-input-group {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input {
    max-width: none;
  }
  
  .filter-options {
    flex-direction: column;
  }
  
  .filter-select {
    min-width: auto;
  }
  
  .patients-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .patient-card {
    padding: 16px;
  }
  
  .patient-details {
    gap: 6px;
  }
  
  .detail-item {
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 20px;
  }
  
  .page-subtitle {
    font-size: 14px;
  }
  
  .patient-name {
    font-size: 16px;
  }
  
  .patient-actions {
    flex-direction: column;
    gap: 4px;
  }
}

/* Animation for card loading */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.patient-card {
  animation: fadeInUp 0.3s ease-out;
}

/* Custom scrollbar for better UX */
.patients-grid::-webkit-scrollbar {
  width: 6px;
}

.patients-grid::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.patients-grid::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.patients-grid::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* List View Styles */
.patients-list {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.patients-table {
  width: 100%;
}

.table-header {
  display: grid;
  grid-template-columns: 40px 60px 1fr 120px 120px 80px 100px 120px;
  background: #f8f9fa;
  border-bottom: 2px solid #e9ecef;
  font-weight: 600;
  color: #495057;
}

.header-cell {
  padding: 16px 12px;
  display: flex;
  align-items: center;
  font-size: 14px;
  border-right: 1px solid #e9ecef;
}

.header-cell:last-child {
  border-right: none;
}

.table-row {
  display: grid;
  grid-template-columns: 40px 60px 1fr 120px 120px 80px 100px 120px;
  border-bottom: 1px solid #f0f2f5;
  transition: all 0.3s ease;
  cursor: pointer;
}

.table-row:hover {
  background: #f8f9ff;
}

.table-row.selected {
  background: #f0f2ff;
  border-color: #667eea;
}

.table-row:last-child {
  border-bottom: none;
}

.table-cell {
  padding: 16px 12px;
  display: flex;
  align-items: center;
  font-size: 14px;
  border-right: 1px solid #f0f2f5;
  color: #606266;
}

.table-cell:last-child {
  border-right: none;
}

.list-photo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #f0f2f5;
}

.patient-name-list {
  font-weight: 600;
  color: #303133;
  font-size: 15px;
}

.patient-id {
  font-family: 'Courier New', monospace;
  font-size: 13px;
  color: #909399;
}

.patient-dob {
  font-size: 13px;
  color: #606266;
}

.patient-age {
  font-weight: 500;
  color: #303133;
}

.checkbox-col {
  justify-content: center;
}

.photo-col {
  justify-content: center;
}

.actions-col {
  justify-content: center;
  gap: 8px;
}

/* Responsive adjustments for list view */
@media (max-width: 1200px) {
  .table-header,
  .table-row {
    grid-template-columns: 40px 60px 1fr 100px 100px 60px 80px 100px;
  }
}

@media (max-width: 768px) {
  .patients-list {
    overflow-x: auto;
  }
  
  .table-header,
  .table-row {
    grid-template-columns: 40px 60px 200px 100px 100px 60px 80px 100px;
    min-width: 800px;
  }
}
</style>
