document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('taskForm');
  const alertBox = document.getElementById('form-alert');
  const submitBtn = document.getElementById('submitBtn');
  const recipientsContainer = document.getElementById('recipientsContainer');
  const addRecipientBtn = document.getElementById('addRecipientBtn');
  const recipientsError = document.getElementById('err_recipients');

  // Initialize EmailJS
  if (typeof emailjs !== 'undefined') {
    emailjs.init({ publicKey: '4ddxDf1zPYW-G2-LS' });
    console.log('✓ EmailJS initialized');
  } else {
    console.error('✗ EmailJS library not found!');
  }

  function showAlert(type, msg) {
    alertBox.className = 'form-alert ' + (type === 'success' ? 'success' : 'error');
    alertBox.textContent = msg;
    alertBox.classList.remove('hidden');
    setTimeout(() => alertBox.classList.add('hidden'), 6000);
  }

  function ensureRemoveButton(row) {
    const emailGroup = row.querySelector('.recipient-email-group');
    if (!emailGroup) {
      return;
    }

    if (!emailGroup.querySelector('.recipient-remove-btn')) {
      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'recipient-remove-btn';
      removeBtn.setAttribute('data-remove-recipient', '1');
      removeBtn.textContent = 'Remove';
      emailGroup.appendChild(removeBtn);
    }
  }

  function reindexRecipientRows() {
    const rows = recipientsContainer.querySelectorAll('[data-recipient-row]');
    rows.forEach(function (row, index) {
      const nameInput = row.querySelector('input[name="recipient_name[]"]');
      const emailInput = row.querySelector('input[name="recipient_email[]"]');
      const nameLabel = row.querySelector('label[for^="recipient_name_"]');
      const emailLabel = row.querySelector('label[for^="recipient_email_"]');

      if (nameInput) {
        nameInput.id = 'recipient_name_' + index;
      }
      if (emailInput) {
        emailInput.id = 'recipient_email_' + index;
      }
      if (nameLabel) {
        nameLabel.setAttribute('for', 'recipient_name_' + index);
      }
      if (emailLabel) {
        emailLabel.setAttribute('for', 'recipient_email_' + index);
      }
    });
  }

  function updateRemoveButtons() {
    const rows = recipientsContainer.querySelectorAll('[data-recipient-row]');
    rows.forEach(function (row, index) {
      const removeBtn = row.querySelector('.recipient-remove-btn');
      if (!removeBtn) {
        return;
      }
      removeBtn.style.display = rows.length === 1 && index === 0 ? 'none' : 'inline-block';
    });
  }

  function addRecipientRow(nameValue, emailValue) {
    const row = document.createElement('div');
    row.className = 'form-row recipient-row';
    row.setAttribute('data-recipient-row', '1');
    row.innerHTML =
      '<div class="form-group">' +
      '  <label>Recipient Name <span class="req">*</span></label>' +
      '  <input type="text" name="recipient_name[]" placeholder="Recipient\'s full name" autocomplete="off" />' +
      '</div>' +
      '<div class="form-group recipient-email-group">' +
      '  <label>Recipient Email <span class="req">*</span></label>' +
      '  <input type="email" name="recipient_email[]" placeholder="recipient@email.com" autocomplete="off" />' +
      '  <button type="button" class="recipient-remove-btn" data-remove-recipient="1">Remove</button>' +
      '</div>';

    const nameInput = row.querySelector('input[name="recipient_name[]"]');
    const emailInput = row.querySelector('input[name="recipient_email[]"]');
    nameInput.value = nameValue || '';
    emailInput.value = emailValue || '';

    recipientsContainer.appendChild(row);
    reindexRecipientRows();
    updateRemoveButtons();
  }

  function resetRecipientsToSingleRow() {
    const rows = recipientsContainer.querySelectorAll('[data-recipient-row]');
    if (rows.length > 1) {
      for (let i = 1; i < rows.length; i++) {
        rows[i].remove();
      }
    }

    const firstRow = recipientsContainer.querySelector('[data-recipient-row]');
    if (firstRow) {
      const firstName = firstRow.querySelector('input[name="recipient_name[]"]');
      const firstEmail = firstRow.querySelector('input[name="recipient_email[]"]');
      if (firstName) {
        firstName.value = '';
        firstName.classList.remove('error');
      }
      if (firstEmail) {
        firstEmail.value = '';
        firstEmail.classList.remove('error');
      }
      ensureRemoveButton(firstRow);
    }

    recipientsError.textContent = '';
    reindexRecipientRows();
    updateRemoveButtons();
  }

  function validateRecipients() {
    const rows = recipientsContainer.querySelectorAll('[data-recipient-row]');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let hasAny = false;
    let hasErrors = false;

    rows.forEach(function (row) {
      const nameInput = row.querySelector('input[name="recipient_name[]"]');
      const emailInput = row.querySelector('input[name="recipient_email[]"]');

      const nameValue = nameInput ? nameInput.value.trim() : '';
      const emailValue = emailInput ? emailInput.value.trim() : '';

      if (nameInput) {
        nameInput.classList.remove('error');
      }
      if (emailInput) {
        emailInput.classList.remove('error');
      }

      if (nameValue !== '' || emailValue !== '') {
        hasAny = true;
      }

      if (nameValue === '' || emailValue === '' || !emailRegex.test(emailValue)) {
        hasErrors = true;
        if (nameInput && nameValue === '') {
          nameInput.classList.add('error');
        }
        if (emailInput && (emailValue === '' || !emailRegex.test(emailValue))) {
          emailInput.classList.add('error');
        }
      }
    });

    if (!hasAny) {
      recipientsError.textContent = 'Please add at least one recipient.';
      return false;
    }

    if (hasErrors) {
      recipientsError.textContent = 'Each recipient row must have a name and a valid email.';
      return false;
    }

    recipientsError.textContent = '';
    return true;
  }

  function sendEmailNotification(basePayload, taskInfo) {
    if (typeof emailjs === 'undefined') {
      console.error('✗ EmailJS not available');
      return Promise.resolve();
    }

    // EMAILJS CONFIG
    var serviceId  = 'service_4si46bk';
    var templateId = 'template_evgvrfc';

    // FORM VALUES
    var senderName     = basePayload.sender_name;
    var senderEmail    = basePayload.sender_email;
    var recipientName  = taskInfo.recipient_name;
    var recipientEmail = taskInfo.recipient_email;
    var taskTitle      = basePayload.task_title;
    var taskDesc       = basePayload.task_description;
    var taskDeadline   = basePayload.task_deadline;
    var taskPriority   = basePayload.task_priority;

    // Validate email format
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(recipientEmail)) {
      console.error('✗ Invalid recipient email format:', recipientEmail);
      showAlert('error', 'Invalid recipient email: ' + recipientEmail);
      return Promise.resolve();
    }

    console.log('📧 Email Details:');
    console.log('   To: ' + recipientEmail);
    console.log('   Task: ' + taskTitle);

    // DATE/TIME
    var now = new Date();
    var sentDate = now.toLocaleDateString([], { year: 'numeric', month: 'long', day: 'numeric' });
    var sentTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    var taskIdValue = String(taskInfo.id || '');
    var siteOrigin = (window.location.protocol ? window.location.protocol : 'http:') + '//' + window.location.host;
    var statusBaseUrl = siteOrigin + '/notification/start_task.php';
    var taskTrackingUrl = siteOrigin + '/notification/status.php#task-' + encodeURIComponent(taskIdValue);

    // TEMPLATE PARAMETERS
    // Note: EmailJS requires "To Email" field in template to be set to {{to_email}}
    var templateParams = {
      to_email: recipientEmail.trim().toLowerCase(),
      receiver_name: recipientName,
      sender_name: senderName,
      sender_email: senderEmail,
      task_title: taskTitle,
      task_description: taskDesc,
      task_deadline: taskDeadline,
      task_priority: taskPriority,
      task_id: taskIdValue,
      date_received: sentDate,
      start_task_link: statusBaseUrl + '?task_id=' + encodeURIComponent(taskIdValue),
      complete_task_link: siteOrigin + '/notification/completed_task.php?task_id=' + encodeURIComponent(taskIdValue),
      completed_task_link: siteOrigin + '/notification/completed_task.php?task_id=' + encodeURIComponent(taskIdValue),
      tracking_link: taskTrackingUrl
    };

    console.log('📝 Sending with params:', templateParams);

    // SEND EMAIL
    return emailjs.send(serviceId, templateId, templateParams)
      .then(function (response) {
        console.log('✓ Email sent successfully to ' + recipientEmail);
        console.log('Response:', response);
        return response;
      })
      .catch(function (err) {
        console.error('✗ Email failed:', err);
        console.error('Service ID:', serviceId);
        console.error('Template ID:', templateId);
        console.error('Recipient Email:', recipientEmail);
        return err;
      });
  }

  const initialRow = recipientsContainer.querySelector('[data-recipient-row]');
  if (initialRow) {
    ensureRemoveButton(initialRow);
    reindexRecipientRows();
    updateRemoveButtons();
  }

  addRecipientBtn.addEventListener('click', function () {
    addRecipientRow('', '');
  });

  recipientsContainer.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-remove-recipient]');
    if (!btn) {
      return;
    }

    const rows = recipientsContainer.querySelectorAll('[data-recipient-row]');
    if (rows.length <= 1) {
      return;
    }

    const row = btn.closest('[data-recipient-row]');
    if (row) {
      row.remove();
      reindexRecipientRows();
      updateRemoveButtons();
      validateRecipients();
    }
  });

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    console.log('📝 Form submitted');

    if (!validateRecipients()) {
      return;
    }

    submitBtn.disabled = true;

    const formData = new FormData(form);

    try {
      console.log('📤 Sending to server...');
      const res = await fetch('assign.php', { method: 'POST', body: formData });
      const json = await res.json();
      
      console.log('📥 Server response:', json);
      
      if (json.success) {
        console.log('✓ Tasks saved:', json.tasks || []);
        const tasks = Array.isArray(json.tasks) ? json.tasks : [];
        const basePayload = {
          sender_name: formData.get('sender_name'),
          sender_email: formData.get('sender_email'),
          task_title: formData.get('task_title'),
          task_description: formData.get('task_description'),
          task_deadline: formData.get('task_deadline'),
          task_priority: formData.get('task_priority')
        };

        if (tasks.length > 0) {
          await Promise.allSettled(tasks.map(function (task) {
            return sendEmailNotification(basePayload, task);
          }));
        }

        showAlert('success', 'Task assigned successfully to ' + (json.count || tasks.length || 1) + ' recipient(s).');
        form.reset();
        resetRecipientsToSingleRow();
      } else if (json.error) {
        console.error('✗ Server error:', json.error);
        showAlert('error', json.error);
      } else {
        console.error('✗ Unexpected response');
        showAlert('error', 'Unexpected server response');
      }
    } catch (err) {
      console.error('✗ Network error:', err);
      showAlert('error', 'Network error: ' + err.message);
    } finally {
      submitBtn.disabled = false;
    }
  });
});