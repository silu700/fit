<div id="content">
    <div class="card mx-auto shadow-sm" style="max-width: 500px;">
        <div class="card-header bg-primary text-white">Dodaj nową wpłatę</div>
        <div class="card-body">
            <form action="save.php" method="POST">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Wybierz użytkownika</label>
                    <select name="user_id" id="userSelect" class="form-select" required>
                        <option value="" selected disabled>-- kliknij, aby wybrać --</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (isset($_GET['user_id']) && $_GET['user_id'] == $u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['nazwisko'] . ' ' . $u['imie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Kwota (PLN)</label>
                    <input type="number" name="kwota" class="form-control" value="150" required>
                </div>

                <button type="submit" id="saveButton" class="btn btn-success w-100" disabled>
                    Zapisz wpłatę
                </button>
                
                <a href="list.php" class="btn btn-link w-100 text-muted mt-2">Anuluj</a>
            </form>
        </div>
    </div>
</div>

<script>
    const userSelect = document.getElementById('userSelect');
    const saveButton = document.getElementById('saveButton');

    // Funkcja sprawdzająca, czy przycisk ma być aktywny
    function checkSelection() {
        if (userSelect.value !== "") {
            saveButton.disabled = false;
        } else {
            saveButton.disabled = true;
        }
    }

    // Sprawdź przy starcie (np. jeśli user_id przyszedł w URL)
    checkSelection();

    // Sprawdzaj przy każdej zmianie w liście rozwijanej
    userSelect.addEventListener('change', checkSelection);
</script>