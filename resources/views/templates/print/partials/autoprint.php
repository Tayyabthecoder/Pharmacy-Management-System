<script>
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            window.print();
        }, 500);
        
        window.onafterprint = () => {
            window.close();
        };
    });
</script>
