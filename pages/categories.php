<?php
/**
 * categories.php (view)
 *
 * Camada de apresentação da página de categorias.
 * Toda a lógica está em categories.logic.php.
 *
 * Variáveis disponíveis na view:
 *  - $action_error, $action_success
 *  - $categories (array)
 *  - $edit_category (array|null)
 */
require_once __DIR__ . '/categories.logic.php';

$page_title  = 'Categorias';
$active_page = 'categories';
include __DIR__ . '/../includes/header.php';
?>

        <div class="page-header">
            <div>
                <h1 class="page-title">Categorias</h1>
                <p class="page-subtitle">Gerencie suas categorias de gastos</p>
            </div>
            <button class="btn-primary btn-small" onclick="openModal()">+ Adicionar Categoria</button>
        </div>

        <?php if (!empty($action_error)): ?>
            <div class="alert alert-error"><?php echo $action_error; ?></div>
        <?php endif; ?>

        <?php if (!empty($action_success)): ?>
            <div class="alert alert-success"><?php echo $action_success; ?></div>
        <?php endif; ?>

        <!-- GRID DE CATEGORIAS -->
        <?php if (empty($categories)): ?>
            <div class="card">
                <p class="empty-state">Nenhuma categoria cadastrada. Crie sua primeira categoria!</p>
            </div>
        <?php else: ?>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card" style="border-top: 4px solid <?php echo $category['color']; ?>;">
                        <div class="category-card-header">
                            <div class="category-card-icon" style="background: <?php echo $category['color']; ?>20;">
                                <?php echo $category['icon']; ?>
                            </div>
                        </div>
                            <h3 class="category-card-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="category-card-total">R$ <?php echo number_format($category['total_spent'], 2, ',', '.'); ?></p>
                            <p class="category-card-count"><?php echo $category['total_expenses']; ?> gasto(s)</p>

                            <div class="category-card-actions">
                                <a href="?edit=<?php echo $category['id']; ?>" class="btn-icon btn-edit">✏️ Editar</a>
                                <a href="?delete=<?php echo $category['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">🗑️ Excluir</a>
                            </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
</div>
    <!-- MODAL -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
        <div class="modal">
            <div class="modal-header">
                <h2><?php echo $edit_category ? 'Editar Categoria' : 'Adicionar Categoria'; ?></h2>
                <button class="modal-close" onclick="closeModal()">✕</button>
            </div>

            <form action="" method="POST">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nome da categoria</label>
                    <input type="text" name="name" placeholder="Ex: Alimentação, Transporte..." value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
    <label>Ícone</label>
    <input type="text" name="icon" id="iconInput" placeholder="Selecione um ícone abaixo" value="<?php echo $edit_category ? $edit_category['icon'] : ''; ?>" readonly>
    <div class="emoji-grid">
        <span class="emoji-option" onclick="selectEmoji('💰')">💰</span>
        <span class="emoji-option" onclick="selectEmoji('🍔')">🍔</span>
        <span class="emoji-option" onclick="selectEmoji('🚗')">🚗</span>
        <span class="emoji-option" onclick="selectEmoji('🏠')">🏠</span>
        <span class="emoji-option" onclick="selectEmoji('🎮')">🎮</span>
        <span class="emoji-option" onclick="selectEmoji('👕')">👕</span>
        <span class="emoji-option" onclick="selectEmoji('💊')">💊</span>
        <span class="emoji-option" onclick="selectEmoji('📚')">📚</span>
        <span class="emoji-option" onclick="selectEmoji('✈️')">✈️</span>
        <span class="emoji-option" onclick="selectEmoji('🐾')">🐾</span>
        <span class="emoji-option" onclick="selectEmoji('💡')">💡</span>
        <span class="emoji-option" onclick="selectEmoji('📱')">📱</span>
        <span class="emoji-option" onclick="selectEmoji('🎵')">🎵</span>
        <span class="emoji-option" onclick="selectEmoji('🏋️')">🏋️</span>
        <span class="emoji-option" onclick="selectEmoji('🍕')">🍕</span>
        <span class="emoji-option" onclick="selectEmoji('☕')">☕</span>
        <span class="emoji-option" onclick="selectEmoji('🛒')">🛒</span>
        <span class="emoji-option" onclick="selectEmoji('💻')">💻</span>
        <span class="emoji-option" onclick="selectEmoji('🎓')">🎓</span>
        <span class="emoji-option" onclick="selectEmoji('🏥')">🏥</span>
        <span class="emoji-option" onclick="selectEmoji('⚡')">⚡</span>
        <span class="emoji-option" onclick="selectEmoji('💧')">💧</span>
        <span class="emoji-option" onclick="selectEmoji('🐶')">🐶</span>
        <span class="emoji-option" onclick="selectEmoji('🎁')">🎁</span>
    </div>
</div>

<div class="form-group">
    <label>Cor</label>
    <input type="color" name="color" value="<?php echo $edit_category ? $edit_category['color'] : '#7C3AED'; ?>" class="input-color">
</div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flag passada do PHP para o JS (indica se o modal abre em modo edicao) -->
    <script>
        const CATEGORY_EDIT_MODE = <?php echo $edit_category ? 'true' : 'false'; ?>;
    </script>
    <script src="../assets/js/categories.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
