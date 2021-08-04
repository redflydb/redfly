function setReminderTimer(milliseconds) {
  if (!(localStorage.getItem('REDflyKey'))) {
    if (document.getElementById('popup_frame') !== null) {
      document.getElementById('popup_frame').style.display = 'block';
      setTimeout(function() {
        document.getElementById('popup_frame').style.display = 'none';
      }, milliseconds);
      localStorage.setItem('REDflyKey', 'true');
    }
  }
}
