<template>
  <div class="login">
    <div class="login-container">
      <!-- Left Side - Image Section -->
      <div class="login-image" :style="{ 'background-image': 'url(' + loginBackground + ')' }">
        <div class="image-overlay">
          <div class="welcome-content">
            <div class="medical-icon">
              <i class="el-icon-first-aid-kit"></i>
            </div>
            <h2 class="welcome-title">Welcome to EMR</h2>
            <h3 class="doctor-name">JOSEPH PETER T. LIM, MD</h3>
            <p class="welcome-subtitle">Electronic Medical Records System</p>
            <div class="features-list">
              <div class="feature-item">
                <i class="el-icon-check"></i>
                <span>Secure Patient Records</span>
              </div>
              <div class="feature-item">
                <i class="el-icon-check"></i>
                <span>Appointment Management</span>
              </div>
              <div class="feature-item">
                <i class="el-icon-check"></i>
                <span>Prescription Tracking</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Right Side - Login Form -->
      <div class="login-content">
        <div class="login-form-container">
          <div class="form-header">
            <div class="logo-section">
              <div class="logo-icon">
                <i class="el-icon-first-aid-kit"></i>
              </div>
              <h1 class="form-title">Sign In</h1>
              <p class="form-subtitle">Access your medical practice dashboard</p>
            </div>
          </div>
          
          <el-form 
            ref="loginForm" 
            :model="loginForm" 
            :rules="loginRules" 
            class="login-form" 
            auto-complete="on" 
            label-position="left"
          >
            <div class="form-fields">
              <el-form-item prop="username" class="form-item">
                <div class="input-group">
                  <div class="input-icon">
                    <i class="el-icon-user"></i>
                  </div>
                  <el-input 
                    v-model="loginForm.username" 
                    type="text" 
                    auto-complete="on" 
                    placeholder="Enter your username"
                    class="custom-input"
                  />
                </div>
              </el-form-item>
              
              <el-form-item prop="password" class="form-item">
                <div class="input-group">
                  <div class="input-icon">
                    <i class="el-icon-lock"></i>
                  </div>
                  <el-input
                    v-model="loginForm.password"
                    name="password"
                    auto-complete="on"
                    placeholder="Enter your password"
                    :type="pwdType"
                    class="custom-input"
                    @keyup.enter.native="handleLogin"
                  />
                  <div class="password-toggle" @click="showPwd">
                    <i :class="pwdType === 'password' ? 'el-icon-view' : 'el-icon-hide'"></i>
                  </div>
                </div>
              </el-form-item>
              
              <div class="form-options">
                <el-checkbox v-model="rememberMe" class="remember-me">
                  Remember me
                </el-checkbox>
                <!-- <a href="#" class="forgot-password" @click.prevent="handleForgotPassword">
                  Forgot password?
                </a> -->
              </div>
              
              <el-form-item class="submit-item">
                <el-button 
                  :loading="loading" 
                  type="primary" 
                  class="login-button"
                  @click.native.prevent="handleLogin"
                >
                  <span v-if="!loading">Sign In</span>
                  <span v-else>Signing In...</span>
                </el-button>
              </el-form-item>
            </div>
          </el-form>
          
          <div class="form-footer">
            <p class="footer-text">
              Secure access to your medical practice management system
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { validEmail } from '@/utils/validate';
import { csrf } from '@/api/auth';
import logo from '@/assets/login/logo.png';
import loginBackground from '@/assets/login/login_background.jpg';

export default {
  name: 'Login',
  data() {
    const validateEmail = (rule, value, callback) => {
      if (!validEmail(value)) {
        callback(new Error('Please enter the correct email'));
      } else {
        callback();
      }
    };
    const validatePass = (rule, value, callback) => {
      if (value.length < 4) {
        callback(new Error('Password cannot be less than 4 digits'));
      } else {
        callback();
      }
    };
    return {
      loginForm: {
        email: '',
        password: '',
        username: '',
      },
      loginRules: {
        username: [{ required: true, trigger: 'blur', message: 'Please enter your username' }],
        password: [{ required: true, trigger: 'blur', validator: validatePass }],
      },
      loading: false,
      pwdType: 'password',
      rememberMe: false,
      redirect: undefined,
      otherQuery: {},
      logo: '\public' + '\\' + logo,
      loginBackground: '\public\\' + loginBackground,
    };
  },
  watch: {
    $route: {
      handler: function(route) {
        const query = route.query;
        if (query) {
          this.redirect = query.redirect;
          this.otherQuery = this.getOtherQuery(query);
        }
      },
      immediate: true,
    },
  },
  methods: {
    showPwd() {
      if (this.pwdType === 'password') {
        this.pwdType = '';
      } else {
        this.pwdType = 'password';
      }
    },
    handleLogin() {
      this.$refs.loginForm.validate(valid => {
        if (valid) {
          this.loading = true;
          csrf().then(() => {
            this.$store.dispatch('user/login', this.loginForm)
              .then(() => {
                this.$router.push({ path: this.redirect || '/', query: this.otherQuery }, onAbort => {});
                this.loading = false;
              })
              .catch(() => {
                this.loading = false;
              });
          });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    getOtherQuery(query) {
      return Object.keys(query).reduce((acc, cur) => {
        if (cur !== 'redirect') {
          acc[cur] = query[cur];
        }
        return acc;
      }, {});
    },
    loginBackgroundImage() {
      return 'url(\'public\\' + this.loginBackground + '\')';
    },
    handleForgotPassword() {
      this.$message.info('Forgot password functionality will be implemented');
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss">
/* Global Element UI overrides */
.login-container {
  .el-input {
    .el-input__inner {
      border: none;
      background: transparent;
      padding: 0;
      height: auto;
      line-height: 1.5;
      color: #2c3e50;
      font-size: 16px;
      
      &::placeholder {
        color: #a0aec0;
        font-weight: 400;
      }
      
      &:focus {
        outline: none;
        box-shadow: none;
      }
    }
  }
  
  .el-form-item {
    margin-bottom: 24px;
    
    &.form-item {
      margin-bottom: 20px;
    }
    
    &.submit-item {
      margin-bottom: 0;
    }
  }
  
  .el-button {
    &.login-button {
      height: 50px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 12px;
      transition: all 0.3s ease;
      
      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
      }
    }
  }
  
  .el-checkbox {
    .el-checkbox__label {
      color: #6c757d;
      font-size: 14px;
      font-weight: 500;
    }
  }
}
</style>

<style rel="stylesheet/scss" lang="scss" scoped>
// Color Variables
$primary-color: #667eea;
$primary-dark: #5a67d8;
$secondary-color: #764ba2;
$success-color: #4ade80;
$text-primary: #2c3e50;
$text-secondary: #6c757d;
$text-light: #a0aec0;
$background-light: #f8f9fa;
$white: #ffffff;
$border-color: #e2e8f0;
$shadow-light: 0 4px 6px rgba(0, 0, 0, 0.05);
$shadow-medium: 0 10px 25px rgba(0, 0, 0, 0.1);
$shadow-heavy: 0 20px 40px rgba(0, 0, 0, 0.15);

.login {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  overflow: hidden;

  // Animated background elements
  &::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: float 20s ease-in-out infinite;
  }

  .login-container {
    background: $white;
    width: 100%;
    max-width: 1000px;
    min-height: 600px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-radius: 20px;
    box-shadow: $shadow-heavy;
    overflow: hidden;
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;

    &:hover {
      transform: translateY(-5px);
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
    }

    // Left Side - Image Section
    .login-image {
      position: relative;
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;

      .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
      }

      .welcome-content {
        text-align: center;
        color: $white;
        max-width: 400px;

        .medical-icon {
          width: 80px;
          height: 80px;
          background: rgba(255, 255, 255, 0.2);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          margin: 0 auto 30px;
          backdrop-filter: blur(10px);
          border: 2px solid rgba(255, 255, 255, 0.3);

          i {
            font-size: 40px;
            color: $white;
          }
        }

        .welcome-title {
          font-size: 2.5rem;
          font-weight: 700;
          margin: 0 0 10px 0;
          text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .doctor-name {
          font-size: 1.3rem;
          font-weight: 600;
          margin: 0 0 15px 0;
          opacity: 0.9;
          letter-spacing: 1px;
        }

        .welcome-subtitle {
          font-size: 1rem;
          margin: 0 0 40px 0;
          opacity: 0.8;
          font-weight: 300;
        }

        .features-list {
          .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.95rem;
            opacity: 0.9;

            i {
              margin-right: 12px;
              font-size: 16px;
              color: $success-color;
            }

            span {
              font-weight: 500;
            }
          }
        }
      }
    }

    // Right Side - Login Form
    .login-content {
      padding: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: $white;

      .login-form-container {
        width: 100%;
        max-width: 400px;

        .form-header {
          text-align: center;
          margin-bottom: 40px;

          .logo-section {
            .logo-icon {
              width: 60px;
              height: 60px;
              background: linear-gradient(135deg, $primary-color 0%, $secondary-color 100%);
              border-radius: 15px;
              display: flex;
              align-items: center;
              justify-content: center;
              margin: 0 auto 20px;
              box-shadow: $shadow-medium;

              i {
                font-size: 28px;
                color: $white;
              }
            }

            .form-title {
              font-size: 2rem;
              font-weight: 700;
              color: $text-primary;
              margin: 0 0 8px 0;
            }

            .form-subtitle {
              font-size: 1rem;
              color: $text-secondary;
              margin: 0;
              font-weight: 400;
            }
          }
        }

        .login-form {
          .form-fields {
            .input-group {
              position: relative;
              display: flex;
              align-items: center;
              background: $background-light;
              border: 2px solid $border-color;
              border-radius: 12px;
              padding: 0 16px;
              transition: all 0.3s ease;
              height: 50px;

              &:focus-within {
                border-color: $primary-color;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                background: $white;
              }

              .input-icon {
                margin-right: 12px;
                color: $text-light;
                font-size: 18px;
                display: flex;
                align-items: center;
              }

              .custom-input {
                flex: 1;
                border: none;
                background: transparent;
                outline: none;
                font-size: 16px;
                color: $text-primary;

                &::placeholder {
                  color: $text-light;
                }
              }

              .password-toggle {
                margin-left: 12px;
                color: $text-light;
                cursor: pointer;
                font-size: 18px;
                display: flex;
                align-items: center;
                transition: color 0.3s ease;

                &:hover {
                  color: $primary-color;
                }
              }
            }

            .form-options {
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin: 20px 0 30px;

              .remember-me {
                .el-checkbox__label {
                  color: $text-secondary;
                  font-size: 14px;
                }
              }

              .forgot-password {
                color: $primary-color;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: color 0.3s ease;

                &:hover {
                  color: $primary-dark;
                  text-decoration: underline;
                }
              }
            }

            .login-button {
              width: 100%;
              background: linear-gradient(135deg, $primary-color 0%, $secondary-color 100%);
              border: none;
              color: $white;
              font-weight: 600;
              letter-spacing: 0.5px;
              text-transform: uppercase;
              box-shadow: $shadow-medium;

              &:hover {
                background: linear-gradient(135deg, $primary-dark 0%, #6b46c1 100%);
                transform: translateY(-2px);
                box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
              }

              &:active {
                transform: translateY(0);
              }
            }
          }
        }

        .form-footer {
          text-align: center;
          margin-top: 30px;

          .footer-text {
            color: $text-light;
            font-size: 14px;
            margin: 0;
            font-weight: 400;
          }
        }
      }
    }
  }
}

// Responsive Design
@media (max-width: 768px) {
  .login {
    padding: 10px;

    .login-container {
      grid-template-columns: 1fr;
      max-width: 400px;
      min-height: auto;

      .login-image {
        min-height: 300px;
        order: 2;

        .welcome-content {
          .welcome-title {
            font-size: 2rem;
          }

          .doctor-name {
            font-size: 1.1rem;
          }

          .features-list {
            .feature-item {
              font-size: 0.9rem;
            }
          }
        }
      }

      .login-content {
        padding: 40px 30px;
        order: 1;
      }
    }
  }
}

@media (max-width: 480px) {
  .login {
    .login-container {
      .login-content {
        padding: 30px 20px;

        .login-form-container {
          .form-header {
            .logo-section {
              .form-title {
                font-size: 1.5rem;
              }
            }
          }
        }
      }
    }
  }
}

// Animations
@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

// Fade in animation for form elements
.form-fields .form-item {
  animation: fadeInUp 0.6s ease-out;
  animation-fill-mode: both;

  &:nth-child(1) { animation-delay: 0.1s; }
  &:nth-child(2) { animation-delay: 0.2s; }
  &:nth-child(3) { animation-delay: 0.3s; }
  &:nth-child(4) { animation-delay: 0.4s; }
}

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
</style>
