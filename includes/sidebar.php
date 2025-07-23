<!-- sidebar.php -->
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  }

  /* Enhanced Sidebar */
  .sidebar {
    width: 80px;
    background: linear-gradient(180deg, #e91e63 0%, #c2185b 50%, #ad1457 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 0;
    box-shadow: 2px 0 20px rgba(233, 30, 99, 0.3);
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 1000;
  }

  .logo {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 30px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
  }

  .logo img {
    width: 35px;
    height: 35px;
    object-fit: contain;
  }

  .nav-menu {
    display: flex;
    flex-direction: column;
    gap: 20px;
    width: 100%;
    align-items: center;
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .nav-item {
    width: 50px;
    height: 50px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.7);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    text-decoration: none;
  }

  .nav-item:hover,
  .nav-item.active {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
  }

  .nav-item.active::after {
    content: '';
    position: absolute;
    right: -20px;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 30px;
    background: white;
    border-radius: 2px;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
  }

  .nav-item i {
    font-size: 20px;
    transition: all 0.3s ease;
  }

  .nav-item:hover i {
    transform: scale(1.1);
  }

  /* Hover tooltip effect */
  .nav-item::before {
    content: attr(data-tooltip);
    position: absolute;
    left: 70px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transform: translateX(-10px);
    transition: all 0.3s ease;
    z-index: 1001;
  }

  .nav-item:hover::before {
    opacity: 1;
    transform: translateX(0);
  }

  /* Add a small arrow to tooltip */
  .nav-item:hover::after {
    content: '';
    position: absolute;
    left: 65px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 5px 5px 5px 0;
    border-color: transparent rgba(0, 0, 0, 0.8) transparent transparent;
    opacity: 1;
    z-index: 1001;
  }

  .nav-item.active:hover::after {
    right: -20px;
    left: auto;
    width: 4px;
    height: 30px;
    background: white;
    border-radius: 2px;
    border: none;
    transform: translateY(-50%);
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .sidebar {
      width: 100%;
      height: 80px;
      flex-direction: row;
      justify-content: center;
      padding: 15px;
      position: fixed;
      bottom: 0;
      top: auto;
    }
    
    .logo {
      margin-bottom: 0;
      margin-right: 20px;
    }
    
    .nav-menu {
      flex-direction: row;
      gap: 15px;
    }
    
    .nav-item::before {
      left: 50%;
      top: -45px;
      transform: translateX(-50%) translateY(10px);
    }
    
    .nav-item:hover::before {
      transform: translateX(-50%) translateY(0);
    }
    
    .nav-item:hover::after,
    .nav-item.active::after {
      display: none;
    }
  }



  @keyframes slideIn {
    from {
      transform: translateX(-100%);
    }
    to {
      transform: translateX(0);
    }
  }

  /* Pulse effect for active state */
  .nav-item.active {
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
    }
    70% {
      box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
    }
    100% {
      box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
    }
  }
</style>

<!-- Sidebar HTML -->
<div class="sidebar">
  <div class="logo">
    <img src="/hookcraftavenue/assets/img/logo.png" alt="Logo">
  </div>
  <ul class="nav-menu">
    <li>
      <a href="/hookcraftavenue/dashboard.php" class="nav-item" data-tooltip="Dashboard">
        <i class="fas fa-home"></i>
      </a>
    </li>
    <li>
      <a href="/hookcraftavenue/includes/user.php" class="nav-item" data-tooltip="Users">
        <i class="fas fa-user"></i>
      </a>
    </li>
    <li>
      <a href="/hookcraftavenue/includes/product.php" class="nav-item" data-tooltip="Products">
        <i class="fas fa-box"></i>
      </a>
    </li>
    <li>
      <a href="/hookcraftavenue/includes/cart.php" class="nav-item" data-tooltip="Cart">
        <i class="fas fa-shopping-cart"></i>
      </a>
    </li>
    <li>
      <a href="/hookcraftavenue/includes/order.php" class="nav-item" data-tooltip="Orders">
        <i class="fas fa-clipboard-list"></i>
      </a>
    </li>
  </ul>
</div>

<!-- Font Awesome (add this once in your main layout <head> or dashboard.php) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Optional: Add this JavaScript for active state management -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get current page URL
    const currentPath = window.location.pathname;
    
    // Remove active class from all nav items
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => item.classList.remove('active'));
    
    // Add active class to current page nav item
    navItems.forEach(item => {
        if (item.getAttribute('href') === currentPath) {
            item.classList.add('active');
        }
    });
});
</script>