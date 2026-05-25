document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('taskForm');
  const alertBox = document.getElementById('form-alert');
  const submitBtn = document.getElementById('submitBtn');

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

  function sendEmailNotification(formData, taskId) {
    if (typeof emailjs === 'undefined') {
      console.error('✗ EmailJS not available');
      return;
    }

    // EMAILJS CONFIG
    var serviceId  = 'service_4si46bk';
    var templateId = 'template_evgvrfc';

    // FORM VALUES
    var senderName     = formData.get('sender_name');
    var senderEmail    = formData.get('sender_email');
    var recipientName  = formData.get('recipient_name');
    var recipientEmail = formData.get('recipient_email');
    var taskTitle      = formData.get('task_title');
    var taskDesc       = formData.get('task_description');
    var taskDeadline   = formData.get('task_deadline');
    var taskPriority   = formData.get('task_priority');

    // Validate email format
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(recipientEmail)) {
      console.error('✗ Invalid recipient email format:', recipientEmail);
      showAlert('error', 'Invalid recipient email: ' + recipientEmail);
      return;
    }

    console.log('📧 Email Details:');
    console.log('   To: ' + recipientEmail);
    console.log('   Task: ' + taskTitle);

    // DATE/TIME
    var now = new Date();
    var sentDate = now.toLocaleDateString([], { year: 'numeric', month: 'long', day: 'numeric' });
    var sentTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    var taskIdValue = String(taskId || '');
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
      complete_task_link: siteOrigin + '/notification/notification/completed_task.php?task_id=' + encodeURIComponent(taskIdValue),
      completed_task_link: siteOrigin + '/notification/notification/completed_task.php?task_id=' + encodeURIComponent(taskIdValue),
      tracking_link: taskTrackingUrl
    };

    console.log('📝 Sending with params:', templateParams);

    // SEND EMAIL
    emailjs.send(serviceId, templateId, templateParams)
      .then(function (response) {
        console.log('✓ Email sent successfully to ' + recipientEmail);
        console.log('Response:', response);
        showAlert('success', 'Email sent to ' + recipientEmail);
      })
      .catch(function (err) {
        console.error('✗ Email failed:', err);
        console.error('Service ID:', serviceId);
        console.error('Template ID:', templateId);
        console.error('Recipient Email:', recipientEmail);
        showAlert('error', 'Email error: ' + (err.text || err.message || JSON.stringify(err)));
      });
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    console.log('📝 Form submitted');
    submitBtn.disabled = true;

    const formData = new FormData(form);

    try {
      console.log('📤 Sending to server...');
      const res = await fetch('assign.php', { method: 'POST', body: formData });
      const json = await res.json();
      
      console.log('📥 Server response:', json);
      
      if (json.success) {
        console.log('✓ Task saved with ID:', json.id);
        showAlert('success', 'Task assigned successfully!');
        sendEmailNotification(formData, json.id);
        form.reset();
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