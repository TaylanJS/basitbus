<?php require '../assets/partials/_admin-check.php'; ?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteriler</title>
        <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <!-- CSS -->
    <?php 
        require '../assets/styles/admin.php';
        require '../assets/styles/admin-options.php';
        $page="customer";
    ?>
</head>
<body>
    <!-- Yönetici başlık dosyalarını gerektirme -->
    <?php require '../assets/partials/_admin-header.php';?>

    <!-- Müşteri Ekle, Düzenle ve Sil -->
    <?php
        /*
            1. Yöneticinin giriş yapmış olup olmadığını kontrol edin
            2. İstek yönteminin POST olup olmadığını kontrol edin
        */
        if($loggedIn && $_SERVER["REQUEST_METHOD"] == "POST")
        {
            if(isset($_POST["submit"]))
            {
                /*
                    Müşteri Ekleme
                    $_POST anahtarının 'submit' olup olmadığını kontrol edin
                */
                // İstemci tarafında doğrulanmalıdır
                $cname = $_POST["cfirstname"] . " " . $_POST["clastname"];
                $cphone = $_POST["cphone"];
        
                $customer_exists = exist_customers($conn,$cname,$cphone);
                $customer_added = false;
        
                if(!$customer_exists)
                {
                    // Yol benzersizdir, devam edin
                    $sql = "INSERT INTO customers (customer_name, customer_phone, customer_created) VALUES ('$cname', '$cphone', current_timestamp());";
                    $result = mysqli_query($conn, $sql);
                    // Otomatik artan id'yi geri verir
                    $autoInc_id = mysqli_insert_id($conn);
                    // Eğer id mevcutsa, 
                    if($autoInc_id)
                    {
                        $code = rand(1,99999);
                        // Benzersiz kullanıcı kimliğini oluşturur
                        $customer_id = "CUST-".$code.$autoInc_id;
                        
                        $query = "UPDATE customers SET customer_id = '$customer_id' WHERE customers.id = $autoInc_id;";
                        $queryResult = mysqli_query($conn, $query);

                        if(!$queryResult)
                            echo "Çalışmıyor";
                    }

                    if($result)
                        $customer_added = true;
                }
    
                if($customer_added)
                {
                    // Başarı bildirimi göster
                    echo '<div class="my-0 alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Başarılı!</strong> Müşteri Eklendi
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
                else{
                    // Hata bildirimi göster
                    echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Hata!</strong> Müşteri zaten mevcut
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
            }
            if(isset($_POST["edit"]))
            {
                // DÜZENLEME YOLLARI
                $cname = $_POST["cname"];
                $cphone = $_POST["cphone"];
                $id = $_POST["id"];
                $id_if_customer_exists = exist_customers($conn,$cname,$cphone);

                if(!$id_if_customer_exists || $id == $id_if_customer_exists)
                {
                    $updateSql = "UPDATE customers SET
                    customer_name = '$cname',
                    customer_phone = '$cphone' WHERE customers.customer_id = '$id';";

                    $updateResult = mysqli_query($conn, $updateSql);
                    $rowsAffected = mysqli_affected_rows($conn);
    
                    $messageStatus = "danger";
                    $messageInfo = "";
                    $messageHeading = "Hata!";
    
                    if(!$rowsAffected)
                    {
                        $messageInfo = "Düzenleme Yapılmadı!";
                    }
    
                    elseif($updateResult)
                    {
                        // Başarı bildirimi göster
                        $messageStatus = "success";
                        $messageHeading = "Başarılı!";
                        $messageInfo = "Müşteri detayları düzenlendi";
                    }
                    else{
                        // Hata bildirimi göster
                        $messageInfo = "İsteğiniz işlenemedi. Tarafımızdan teknik sorunlar nedeniyle işlem yapılamadı. Oluşan rahatsızlık için özür dileriz";
                    }
    
                    // MESAJ
                    echo '<div class="my-0 alert alert-'.$messageStatus.' alert-dismissible fade show" role="alert">
                    <strong>'.$messageHeading.'</strong> '.$messageInfo.'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
                else{
                    // Eğer müşteri detayları zaten varsa
                    echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Hata!</strong> Müşteri zaten mevcut
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }

            }
            if(isset($_POST["delete"]))
            {
                // SİLME YOLLARI
                $id = $_POST["id"];
                // id => id olan rotayı sil
                $deleteSql = "DELETE FROM customers WHERE customers.id = $id";

                $deleteResult = mysqli_query($conn, $deleteSql);
                $rowsAffected = mysqli_affected_rows($conn);
                $messageStatus = "danger";
                $messageInfo = "";
                $messageHeading = "Hata!";

                if(!$rowsAffected)
                {
                    $messageInfo = "Kayıt Mevcut Değil";
                }

                elseif($deleteResult)
                {   
                    $messageStatus = "success";
                    $messageInfo = "Müşteri Detayları silindi";
                    $messageHeading = "Başarılı!";
                }
                else{

                    $messageInfo = "İsteğiniz işlenemedi. Tarafımızdan teknik sorunlar nedeniyle işlem yapılamadı. Oluşan rahatsızlık için özür dileriz";
                }

                // Mesaj
                echo '<div class="my-0 alert alert-'.$messageStatus.' alert-dismissible fade show" role="alert">
                <strong>'.$messageHeading.'</strong> '.$messageInfo.'
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        }
        ?>
        <?php
            $resultSql = "SELECT * FROM customers ORDER BY customer_created DESC";
                            
            $resultSqlResult = mysqli_query($conn, $resultSql);

            if(!mysqli_num_rows($resultSqlResult)){ ?>
                <!-- Müşteriler mevcut değil -->
                <div class="container mt-4">
                    <div id="noCustomers" class="alert alert-dark " role="alert">
                        <h1 class="alert-heading">Müşteri Bulunamadı!!</h1>
                        <p class="fw-light">İlk ekleyen siz olun!</p>
                        <hr>
                        <div id="addCustomerAlert" class="alert alert-success" role="alert">
                                Eklemek için <button id="add-button" class="button btn-sm"type="button"data-bs-toggle="modal" data-bs-target="#addModal">EKLE <i class="fas fa-plus"></i></button> butonuna tıklayın!
                        </div>
                    </div>
                </div>
            <?php }
            else { ?>   
            <!-- Eğer Müşteriler mevcutsa -->
            <section id="customer">
                <div id="head">
                    <h4>Müşteri Durumu</h4>
                </div>
                <div id="customer-results">
                    <div>
                        <button id="add-button" class="button btn-sm"type="button"data-bs-toggle="modal" data-bs-target="#addModal">Müşteri Detayları Ekle <i class="fas fa-plus"></i></button>
                    </div>
                    <table class="table table-hover table-bordered">
                        <thead>
                            <th>ID</th>
                            <th>İsim</th>
                            <th>İletişim</th>
                            <th>İşlemler</th>
                        </thead>
                        <?php
                            while($row = mysqli_fetch_assoc($resultSqlResult))
                            {
                                    // echo "<pre>";
                                    // var_export($row);
                                    // echo "</pre>";
                                $id = $row["id"];
                                $customer_id = $row["customer_id"];
                                $customer_name = $row["customer_name"];
                                $customer_phone = $row["customer_phone"]; 
                        ?>
                        <tr>
                            <td>
                                <?php
                                    echo $customer_id;
                                ?>
                            </td>
                            <td>
                                <?php
                                    echo $customer_name;
                                ?>
                            </td>
                            <td>
                                <?php
                                    echo $customer_phone;
                                ?>
                            </td>
                            <td>
                            <button class="button edit-button " data-link="<?php echo $_SERVER['REQUEST_URI']; ?>" data-id="<?php 
                                                echo $customer_id;?>" data-name="<?php 
                                                echo $customer_name;?>" data-phone="<?php 
                                                echo $customer_phone;?>"
                                                >Düzenle</button>
                                            <button class="button delete-button" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php 
                                                echo $id;?>">Sil</button>
                            </td>
                        </tr>
                    <?php 
                        }
                    ?>
                    </table>
                </div>
            </section>
            <?php } ?>   
        </div>
    </main>
    <!-- Tüm Modallar Burada -->
    <!-- Müşteri Ekleme Modalı -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Müşteri Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addCustomerForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
                            <div class="mb-3">
                                <label for="cfirstname" class="form-label">Müşteri Adı</label>
                                <input type="text" class="form-control" id="cfirstname" name="cfirstname">
                            </div>
                            <div class="mb-3">
                                <label for="clastname" class="form-label">Müşteri Soyadı</label>
                                <input type="text" class="form-control" id="clastname" name="clastname">
                            </div>
                            <div class="mb-3">
                                <label for="cphone" class="form-label">İletişim Numarası</label>
                                <input type="tel" class="form-control" id="cphone" name="cphone">
                            </div>
                            <button type="submit" class="btn btn-success" name="submit">Gönder</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <!-- Bir şey ekle -->
                    </div>
                    </div>
                </div>
        </div>
        <!-- Silme Modalı -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-circle"></i></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h2 class="text-center pb-4">
                    Emin misiniz?
                </h2>
                <p>
                    Gerçekten bu müşteri detaylarını silmek istiyor musunuz? <strong>Bu işlem geri alınamaz.</strong>
                </p>
                <!-- id'yi geçirmek için gerekli -->
                <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="delete-form"  method="POST">
                    <input id="delete-id" type="hidden" name="id">
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" form="delete-form" name="delete" class="btn btn-danger">Sil</button>
            </div>
            </div>
        </div>
    </div>
    <!-- Harici JS -->
    <script src="../assets/scripts/admin_customer.js"></script>
    <!-- Seçenek 1: Popper ile Bootstrap Paketi -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
</html>