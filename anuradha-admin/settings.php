<?php
include('includes/db.php');
include('includes/header.php');
?>

<div class="d-flex">
    <?php include('includes/sidebar.php'); ?>

    <div class="container py-4">
        <h3 class="mb-4">Settings</h3>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="accordion" id="settingsAccordion">

                    <!-- 1. Business Info -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingBusiness">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBusiness" aria-expanded="true" aria-controls="collapseBusiness">
                                1. Business Info
                            </button>
                        </h2>
                        <div id="collapseBusiness" class="accordion-collapse collapse show" aria-labelledby="headingBusiness" data-bs-parent="#settingsAccordion">
                            <div class="accordion-body">
                                <form>
                                    <div class="mb-3">
                                        <label for="businessName" class="form-label">Business Name</label>
                                        <input type="text" class="form-control" id="businessName" placeholder="Anuradha Hardware">
                                    </div>
                                    <div class="mb-3">
                                        <label for="businessEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="businessEmail" placeholder="info@example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="businessPhone" class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="businessPhone" placeholder="011-2345678">
                                    </div>
                                    <div class="mb-3">
                                        <label for="businessAddress" class="form-label">Address</label>
                                        <textarea class="form-control" id="businessAddress" rows="2"></textarea>
                                    </div>
                                    <button class="btn btn-primary">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Admin Account -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingAdmin">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdmin" aria-expanded="false" aria-controls="collapseAdmin">
                                2. Admin Account
                            </button>
                        </h2>
                        <div id="collapseAdmin" class="accordion-collapse collapse" aria-labelledby="headingAdmin" data-bs-parent="#settingsAccordion">
                            <div class="accordion-body">
                                <form>
                                    <div class="mb-3">
                                        <label for="adminUsername" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="adminUsername" placeholder="admin">
                                    </div>
                                    <div class="mb-3">
                                        <label for="adminPassword" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="adminPassword">
                                    </div>
                                    <button class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Theme Settings -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTheme">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTheme" aria-expanded="false" aria-controls="collapseTheme">
                                3. Theme Settings
                            </button>
                        </h2>
                        <div id="collapseTheme" class="accordion-collapse collapse" aria-labelledby="headingTheme" data-bs-parent="#settingsAccordion">
                            <div class="accordion-body">
                                <form>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="darkModeSwitch">
                                        <label class="form-check-label" for="darkModeSwitch">Enable Dark Mode</label>
                                    </div>
                                    <button class="btn btn-primary mt-3">Apply Theme</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Backup & Restore -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingBackup">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBackup" aria-expanded="false" aria-controls="collapseBackup">
                                5. Backup & Restore
                            </button>
                        </h2>
                        <div id="collapseBackup" class="accordion-collapse collapse" aria-labelledby="headingBackup" data-bs-parent="#settingsAccordion">
                            <div class="accordion-body">
                                <form method="post" action="backup.php">
                                    <button class="btn btn-success me-2">Download Backup</button>
                                </form>
                                <form method="post" action="restore.php" enctype="multipart/form-data" class="mt-3">
                                    <label for="restoreFile" class="form-label">Restore from file</label>
                                    <input type="file" class="form-control" id="restoreFile" name="restore_file">
                                    <button class="btn btn-warning mt-2">Restore</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 6. System Settings -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSystem">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSystem" aria-expanded="false" aria-controls="collapseSystem">
                                6. System Settings
                            </button>
                        </h2>
                        <div id="collapseSystem" class="accordion-collapse collapse" aria-labelledby="headingSystem" data-bs-parent="#settingsAccordion">
                            <div class="accordion-body">
                                <form>
                                    <div class="mb-3">
                                        <label for="currency" class="form-label">Currency</label>
                                        <select class="form-select" id="currency">
                                            <option value="LKR">LKR</option>
                                            <option value="USD">USD</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="timezone" class="form-label">Time Zone</label>
                                        <select class="form-select" id="timezone">
                                            <option value="Asia/Colombo">Asia/Colombo</option>
                                            <option value="Asia/Kolkata">Asia/Kolkata</option>
                                            <option value="UTC">UTC</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-primary">Update Settings</button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div> <!-- /.accordion -->
            </div> <!-- /.card-body -->
        </div> <!-- /.card -->
    </div> <!-- /.container -->
</div>

<?php include('includes/footer.php'); ?>