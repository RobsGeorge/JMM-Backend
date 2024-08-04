// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Pie Chart Example
var ctx = document.getElementById("myPieChart");
var myPieChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ["براعم", "أشبال", "زهرات", "كشافة", "مرشدات", "متقدم", "رائدات", "جوالة", "قادة"],
    datasets: [{
      data: [7,12,5,20,18,15,17,6],
      backgroundColor: ['#e74a3b','#f6c23e', '#2e59d9', '#36b9cc', '#1cc88a', '#C74EDF', '#0ED1F3', '#634561'],
      hoverBackgroundColor: ['#E43B2C','#E7B024', '#204BCC', '#15C8E4', '#18AF78', '#9431A8', '#22A8C0', '#423041'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  },
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
    },
    legend: {
      display: false
    },
    cutoutPercentage: 70,
  },
});
