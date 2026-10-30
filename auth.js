const renderAccountWithLocalStorage = renderAccount;

renderAccount = function () {
  renderAccountWithLocalStorage();

  const form = $('authForm');
  if (!form) return;

  const demoHint = $('accountContent').querySelector('.demo-hint');
  if (demoHint) {
    demoHint.innerHTML = '<b>Demo admin access</b><br>Email: admin@threadline.test<br>Password: admin123<br><br>Customer accounts are saved to the MySQL database.';
  }

  const passwordInput = form.elements.namedItem('password');
  if (authMode === 'register' && passwordInput instanceof HTMLInputElement) {
    passwordInput.minLength = 8;
    passwordInput.placeholder = 'At least 8 characters';
  }

  form.onsubmit = async event => {
    event.preventDefault();
    const formData = new FormData(form);
    const email = String(formData.get('email')).trim().toLowerCase();
    const password = String(formData.get('password'));

    if (email === 'admin@threadline.test' && password === 'admin123') {
      session = { id: 'admin', name: 'Store Admin', email, role: 'admin' };
      saveSession();
      renderAccount();
      toast('Welcome, admin.');
      return;
    }

    const action = authMode === 'register' ? 'register' : 'login';
    const payload = { email, password };
    if (action === 'register') {
      payload.name = String(formData.get('name')).trim();
    }

    const submitButton = form.querySelector('button[type="submit"], button:not([type])');
    if (submitButton) submitButton.disabled = true;

    try {
      const response = await fetch(`api.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const result = await response.json();
      if (!response.ok) {
        throw new Error(result.error || 'The account request failed.');
      }

      session = { ...result.user, role: 'user' };
      saveSession();
      renderAccount();
      toast(action === 'register' ? 'Account created and saved.' : 'Welcome back.');
    } catch (error) {
      toast(error instanceof Error ? error.message : 'Could not connect to the account service.');
    } finally {
      if (submitButton && submitButton.isConnected) submitButton.disabled = false;
    }
  };
};
