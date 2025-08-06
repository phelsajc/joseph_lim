<template>
    <div v-if="visible" class="loading-overlay">
      <div class="loading-spinner"></div>
      <p class="loading-text">{{ text }}</p>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      visible: Boolean, // Controls whether the loading overlay is visible
      text: {
        type: String,
        default: 'Loading...', // Default text if not provided
      },
    },
  };
  </script>
  
  <style scoped>
  .loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    font-size: 18px;
    z-index: 9999;
  }
  
  .loading-spinner {
    width: 50px;
    height: 50px;
    border: 5px solid white;
    border-top: 5px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 10px;
  }
  
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  </style>
  