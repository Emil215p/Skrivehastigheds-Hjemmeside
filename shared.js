Object.defineProperty(window, 'admin', {
    get() {
      window.location.href = 'admin.php';
      return undefined;
    }
  });