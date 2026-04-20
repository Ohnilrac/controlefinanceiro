<?php
/**
 * settings.php (view)
 *
 * Camada de apresentação da página de configurações.
 * Toda a lógica está em settings.logic.php.
 *
 * Variáveis disponíveis na view:
 *  - $user (array)
 *  - $categories (array)
 *  - $error, $success (string)
 */
require_once __DIR__ . '/settings.logic.php';

$page_title  = 'Configurações';
$active_page = 'settings';
include __DIR__ . '/../includes/header.php';
?>

        <div class="page-header">
            <div>
                <h1 class="page-title">Configurações</h1>
                <p class="page-subtitle">Gerencie suas preferências e dados pessoais</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- PERFIL E SEGURANÇA -->
        <div class="card">
            <h2 class="card-title">Perfil & Segurança</h2>
            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nome completo</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Nome de usuário</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <div class="settings-divider">
                    <span>Alterar senha (opcional)</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Senha atual</label>
                        <input type="password" name="current_password" placeholder="Digite sua senha atual">
                    </div>
                    <div class="form-group">
                        <label>Nova senha</label>
                        <input type="password" name="new_password" placeholder="Digite a nova senha">
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirmar nova senha</label>
                    <input type="password" name="confirm_password" placeholder="Confirme a nova senha">
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn-primary btn-small">Salvar alterações</button>
                </div>
            </form>
        </div>

        <!-- SALÁRIO MENSAL -->
        <div class="card">
            <h2 class="card-title">Salário Mensal</h2>
            <p class="card-description">Defina seu salário mensal para acompanhar o quanto você já gastou.</p>
            <form action="" method="POST">
                <div class="salary-form">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <input type="text" name="salary" id="salaryInput" class="money-input" inputmode="numeric" placeholder="R$ 0,00" value="R$ <?php echo number_format($user['salary'], 2, ',', '.'); ?>">
                    </div>
                    <button type="submit" name="update_salary" class="btn-primary btn-small">Atualizar</button>
                </div>
            </form>
        </div>

        <!-- ORÇAMENTO POR CATEGORIA -->
        <div class="card" style="margin-bottom: 64px;">
        <h2 class="card-title">Orçamento por Categoria</h2>
        <p class="card-description">Defina um limite de gastos para cada categoria.</p>
        <?php if (empty($categories)): ?>
        <p class="empty-state">Nenhuma categoria cadastrada. <a href="categories.php" style="color: #7C3AED;">Criar categorias</a></p>
        <?php else: ?>
        <form action="" method="POST">
            <div class="budget-table-wrapper">

            <table class="table">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Orçamento</th>
                        <th>Total Gasto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <span style="margin-right: 8px;"><?php echo $category['icon']; ?></span>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </td>
                            <td>
                                <input type="text"
                                    name="budget[<?php echo $category['id']; ?>]"
                                    class="budget-input money-input"
                                    inputmode="numeric"
                                    placeholder="R$ 0,00"
                                    value="<?php echo $category['budget'] > 0 ? 'R$ ' . number_format($category['budget'], 2, ',', '.') : ''; ?>">
                            </td>
                            <td>
                            <?php $over_budget = $category['budget'] > 0 && $category['total_spent'] > $category['budget'];?>
                                <span class="badge" style="<?php echo $over_budget ? 'background: rgba(220,38,38,0.15); color: #dc2626; border: 1px solid rgba(220,38,38,0.4);' : 'background: ' . $category['color'] . '25; color: ' . $category['color'] . '; border: 1px solid ' . $category['color'] . '50;'; ?>">
                                    <?php echo $over_budget ? '⚠️ ' : ''; ?>R$ <?php echo number_format($category['total_spent'], 2, ',', '.'); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            </div>
            <div class="form-actions" style="margin-top: 24px;">
                <button type="submit" name="update_budget" class="btn-primary btn-small">Salvar Orçamentos</button>
            </div>
        </form>
        <?php endif; ?>
        </div>
    </main>
</div>


    <script src="../assets/js/settings.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
