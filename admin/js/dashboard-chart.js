/**
 * Lightweight Canvas Chart Implementation
 * Draws a simple bar chart for order statistics
 */
class DashboardChart {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas.getContext('2d');
        this.padding = 40;
        this.gridColor = 'rgba(255, 255, 255, 0.1)';
        this.barColors = {
            'Pending': '#f59e0b',   // Orange/Yellow
            'Cancelled': '#ef4444', // Red
            'Completed': '#10b981'  // Green
        };

        // Handle resizing
        window.addEventListener('resize', () => this.draw());
    }

    setData(data) {
        this.data = data;
        this.draw();
    }

    draw() {
        if (!this.data) return;

        // Reset canvas size to match display size
        const rect = this.canvas.parentElement.getBoundingClientRect();
        this.canvas.width = rect.width;
        this.canvas.height = 300; // Fixed height

        const width = this.canvas.width;
        const height = this.canvas.height;
        const ctx = this.ctx;

        // Clear canvas
        ctx.clearRect(0, 0, width, height);

        // Calculate dimensions
        const chartHeight = height - this.padding * 2;
        const chartWidth = width - this.padding * 2;
        const startX = this.padding;
        const startY = height - this.padding;

        // Find max value for scaling
        const maxValue = Math.max(...Object.values(this.data), 5); // Minimum scale of 5
        const scale = chartHeight / maxValue;

        // Draw grid lines
        ctx.strokeStyle = this.gridColor;
        ctx.lineWidth = 1;
        ctx.beginPath();

        // Vertical axis line
        ctx.moveTo(startX, this.padding);
        ctx.lineTo(startX, startY);

        // Horizontal axis line
        ctx.lineTo(startX + chartWidth, startY);
        ctx.stroke();

        // Draw Bars
        const labels = Object.keys(this.data);
        const barWidth = Math.min(60, (chartWidth / labels.length) * 0.5);
        const gap = (chartWidth - (barWidth * labels.length)) / (labels.length + 1);

        labels.forEach((label, index) => {
            const value = this.data[label];
            const barHeight = value * scale;
            const x = startX + gap + (index * (barWidth + gap));
            const y = startY - barHeight;

            // Draw Bar
            ctx.fillStyle = this.barColors[label] || '#9ca3af';

            // Rounded corners for top
            ctx.beginPath();
            ctx.roundRect(x, y, barWidth, barHeight, [4, 4, 0, 0]);
            ctx.fill();

            // Draw Label
            ctx.fillStyle = '#9ca3af';
            ctx.font = '12px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(label, x + barWidth / 2, startY + 20);

            // Draw Value
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 14px Inter, sans-serif';
            ctx.fillText(value, x + barWidth / 2, y - 8);
        });
    }
}
