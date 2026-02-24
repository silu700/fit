</div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Tu możesz dodać np. potwierdzenie usuwania rekordów
    document.querySelectorAll('.btn-outline-danger').forEach(button => {
        button.onclick = function() {
            return confirm('Czy na pewno chcesz to usunąć?');
        };
    });
</script>

</body>
</html>