// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Pie Chart Example
var ctx = document.getElementById("myPieChart2");
var myPieChart2 = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ["سواعد", "مساعد قائد", "قائد فريق", "قائد قطاع", "قائد عام", "نائب قائد عام", "مجلس الصخرة"],
    datasets: [{
      data: [35,25,20,10,1,2,8],
      backgroundColor: ['#e74a3b','#f6c23e', '#2e59d9', '#F30E66','#36b9cc', '#1cc88a', '#C74EDF'],
      hoverBackgroundColor: ['#E43B2C','#E7B024', '#204BCC', '#AF104D','#15C8E4', '#18AF78', '#9431A8'],
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
      caretPadding: 20,
    },
    legend: {
      display: false
    },
    cutoutPercentage: 70,
  },
});
