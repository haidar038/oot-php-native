<?php
session_start();
include_once '../config/database.php';
include_once '../classes/Settings.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$settings = new Settings($db);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settings->site_name = $_POST['site_name'];
    $settings->site_description = $_POST['site_description'];
    $settings->contact_email = $_POST['contact_email'];

    if ($settings->update()) {
        $_SESSION['success'] = "Settings updated successfully";
        header("Location: settings.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update settings";
    }
}

// Get current settings
$current_settings = $settings->getSettings();

include_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Settings</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                    unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="site_name" class="form-label">Site Name</label>
                    <input type="text" class="form-control" id="site_name" name="site_name"
                        value="<?php echo $current_settings['site_name']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="site_description" class="form-label">Site Description</label>
                    <textarea class="form-control" id="site_description" name="site_description"
                        rows="3"><?php echo $current_settings['site_description']; ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="contact_email" class="form-label">Contact Email</label>
                    <input type="email" class="form-control" id="contact_email" name="contact_email"
                        value="<?php echo $current_settings['contact_email']; ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </main>
    </div>
</div>

<?php include_once '../includes/admin_footer.php'; ?>