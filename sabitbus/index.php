<?php
    require 'assets/partials/_functions.php';
    $conn = db_connect();    

    if(!$conn) 
        die("Connection Failed");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otobüs Bilet Rezervasyonları</title>
    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500&display=swap" rel="stylesheet">
    <!-- Font-awesome -->
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <!-- CSS -->
    <?php 
        require 'assets/styles/styles.php'
    ?>
</head>
<body>
    <?php
    
    if(isset($_GET["booking_added"]) && !isset($_POST['pnr-search']))
    {
        if($_GET["booking_added"])
        {
            echo '<div class="my-0 alert alert-success alert-dismissible fade show" role="alert">
                <strong>Successful!</strong> Booking Added, your PNR is <span style="font-weight:bold; color: #272640;">'. $_GET["pnr"] .'</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        }
        else{
            // Show error alert
            echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Booking already exists
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }

    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["pnr-search"]))
    {
        $pnr = $_POST["pnr"];

        $sql = "SELECT * FROM bookings WHERE booking_id='$pnr'";
        $result = mysqli_query($conn, $sql);

        $num = mysqli_num_rows($result);

        if($num)
        {
            $row = mysqli_fetch_assoc($result);
            $route_id = $row["route_id"];
            $customer_id = $row["customer_id"];
            
            $customer_name = get_from_table($conn, "customers", "customer_id", $customer_id, "customer_name");

            $customer_phone = get_from_table($conn, "customers", "customer_id", $customer_id, "customer_phone");

            $customer_route = $row["customer_route"];
            $booked_amount = $row["booked_amount"];

            $booked_seat = $row["booked_seat"];
            $booked_timing = $row["booking_created"];

            $dep_date = get_from_table($conn, "routes", "route_id", $route_id, "route_dep_date");

            $dep_time = get_from_table($conn, "routes", "route_id", $route_id, "route_dep_time");

            $bus_no = get_from_table($conn, "routes", "route_id", $route_id, "bus_no");
            ?>

            <div class="alert alert-dark alert-dismissible fade show" role="alert">
            
            <h4 class="alert-heading">Bilet Bilgileri!</h4>
            <p>
                <button class="btn btn-sm btn-success"><a href="assets/partials/_download.php?pnr=<?php echo $pnr; ?>" class="link-light">İndir</a></button>
                <button class="btn btn-danger btn-sm" id="deleteBooking" data-bs-toggle="modal" data-bs-target="#deleteModal" data-pnr="<?php echo $pnr;?>" data-seat="<?php echo $booked_seat;?>" data-bus="<?php echo $bus_no; ?>">
                    Sil
                </button>
            </p>
            <hr>
                <p class="mb-0">
                    <ul class="pnr-details">
                        <li>
                            <strong>PNR : </strong>
                            <?php echo $pnr; ?>
                        </li>
                        <li>
                            <strong>Müşteri Adı : </strong>
                            <?php echo $customer_name; ?>
                        </li>
                        <li>
                            <strong>Müşteri Numarası : </strong>
                            <?php echo $customer_phone; ?>
                        </li>
                        <li>
                            <strong>Rota : </strong>
                            <?php echo $customer_route; ?>
                        </li>
                        <li>
                            <strong>Otobüs Kodu : </strong>
                            <?php echo $bus_no; ?>
                        </li>
                        <li>
                            <strong>Koltuk Numarası : </strong>
                            <?php echo $booked_seat; ?>
                        </li>
                        <li>
                            <strong>Gidiş Tarihi : </strong>
                            <?php echo $dep_date; ?>
                        </li>
                        <li>
                            <strong>Kalkış Saati : </strong>
                            <?php echo $dep_time; ?>
                        </li>
                        <li>
                            <strong>Rezervasyon Zamanlaması : </strong>
                            <?php echo $booked_timing; ?>
                        </li>

                </p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php }
        else{
            echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Record Doesnt Exist
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        }
        
    ?>
        
    <?php }


        // Delete Booking
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteBtn"]))
        {
            $pnr = $_POST["id"];
            $bus_no = $_POST["bus"];
            $booked_seat = $_POST["booked_seat"];

            $deleteSql = "DELETE FROM `bookings` WHERE `bookings`.`booking_id` = '$pnr'";

                $deleteResult = mysqli_query($conn, $deleteSql);
                $rowsAffected = mysqli_affected_rows($conn);
                $messageStatus = "danger";
                $messageInfo = "";
                $messageHeading = "Error!";

                if(!$rowsAffected)
                {
                    $messageInfo = "Record Doesn't Exist";
                }

                elseif($deleteResult)
                {   
                    $messageStatus = "success";
                    $messageInfo = "Bilet Detayları Silindi";
                    $messageHeading = "Başarılı!";

                    // Update the Seats table
                    $seats = get_from_table($conn, "seats", "bus_no", $bus_no, "seat_booked");

                    // Extract the seat no. that needs to be deleted
                    $booked_seat = $_POST["booked_seat"];

                    $seats = explode(",", $seats);
                    $idx = array_search($booked_seat, $seats);
                    array_splice($seats,$idx,1);
                    $seats = implode(",", $seats);

                    $updateSeatSql = "UPDATE `seats` SET `seat_booked` = '$seats' WHERE `seats`.`bus_no` = '$bus_no';";
                    mysqli_query($conn, $updateSeatSql);
                }
                else{

                    $messageInfo = "Your request could not be processed due to technical Issues from our part. We regret the inconvenience caused";
                }

                // Message
                echo '<div class="my-0 alert alert-'.$messageStatus.' alert-dismissible fade show" role="alert">
                <strong>'.$messageHeading.'</strong> '.$messageInfo.'
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
    ?>

    
    <header>
        <nav>
            <div>
                    <a href="#" class="nav-item nav-logo">SABITBUS</a>
                    <!-- <a href="#" class="nav-item">Gallery</a> -->
            </div>
                
            <ul>
                <li><a href="#" class="nav-item">ANASAYFA</a></li>
                <li><a href="#about" class="nav-item">HAKKIMIZDA</a></li>
                <li><a href="#contact" class="nav-item">İLETİŞİM</a></li>
            </ul>
            <div>
                <a href="#" class="login nav-item" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fas fa-sign-in-alt" style="margin-right: 0.4rem;"></i>GİRİŞ</a>
                <a href="#pnr-enquiry" class="pnr nav-item">PNR kodu</a>
            </div>
        </nav>
    </header>
    <!-- Login Modal -->
    <?php require 'assets/partials/_loginModal.php'; 
        require 'assets/partials/_getJSON.php';

        $routeData = json_decode($routeJson);
        $busData = json_decode($busJson);
        $customerData = json_decode($customerJson);
    ?>
    

    <section id="home">
        <div id="route-search-form">
            <h1>Otobüs Bilet Sistemi</h1>

            <p class="text-center">Otobüs Bileti Rezervasyon Sistemine Hoş Geldiniz. Otobüs biletlerini ve çok daha fazlasını yönetmek için şimdi giriş yapın. VEYA Yolcu Adı Kaydını (PNR numarası) kullanarak Bilet durumunu kontrol etmek için aşağı kaydırmanız yeterlidir.</p>

            <center>
                <button class="btn btn-danger " data-bs-toggle="modal" data-bs-target="#loginModal">Admin Girişi</button>
                
            </center>

            <br>
            <center>
            <a href="#pnr-enquiry"><button class="btn btn-primary">Sayfayı Kaydır<i class="fa fa-arrow-down"></i></button></a>
            </center>
            
        </div>
    </section>
    <div id="block">
        <section id="info-num">
            <figure>
                <img src="assets/img/route.svg" alt="Bus Route Icon" width="100px" height="100px">
                <figcaption>
                    <span class="num counter" data-target="<?php echo count($routeData); ?>">999</span>
                    <span class="icon-name">Rota</span>
                </figcaption>
            </figure>
            <figure>
                <img src="assets/img/bus.svg" alt="Bus Icon" width="100px" height="100px">
                <figcaption>
                    <span class="num counter" data-target="<?php echo count($busData); ?>">999</span>
                    <span class="icon-name">OTOBÜS</span>
                </figcaption>
            </figure>
            <figure>
                <img src="assets/img/customer.svg" alt="Happy Customer Icon" width="100px" height="100px">
                <figcaption>
                    <span class="num counter" data-target="<?php echo count($customerData); ?>">999</span>
                    <span class="icon-name">MÜŞTERİ MEMNUNİYETİ</span>
                </figcaption>
            </figure>
            <figure>
                <img src="assets/img/ticket.svg" alt="Instant Ticket Icon" width="100px" height="100px">
                <figcaption>
                    <span class="num"><span class="counter" data-target="20">999</span> SEC</span> 
                    <span class="icon-name">HIZLI BİLET</span>
                </figcaption>
            </figure>
        </section>
        <section id="pnr-enquiry">
            <div id="pnr-form">
                <h2>PNR KODUNUZU GİRİN</h2>
                <form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="POST">
                    <div>
                        <input type="text" name="pnr" id="pnr" placeholder="Enter PNR">
                    </div>
                    <button type="submit" name="pnr-search">Submit</button>
                </form>
            </div>
        </section>
        <section id="about">
            <div>
                <h1>Hakkımızda</h1>
                <h4>Otobüs Bileti Almak için saatler harcamaktan sıkıldın mı?</h4>
                <p>
                    En Hızlı ve En Güvenilir Otobüs Bileti Alma Uygulaması Sizlerle Artık herkes saniyeler içinde otobüs bileti alabilecek ve uzun kuyruklar artık hayatımızda olmayacak.
                </p>
            </div>
        </section>
        <section id="contact">
            <div id="contact-form">
                <h1>İletişim</h1>
                <form action="">
                    <div>
                        <label for="name">Adınızı Girin</label>
                        <input type="text" name="name" id="name">
                    </div>
                    <div>
                        <label for="email">Eposta Adresinizi Girin</label>
                        <input type="email" name="email" id="email">
                    </div>
                    <div>
                        <label for="message">Mesajınız</label>
                        <textarea name="message" id="message" cols="30" rows="10"></textarea>
                    </div>
                    <div>
                    <a><button class="btn btn-primary">Gönder</i></button></a>
                    </div>
                    <div></div>
                </form>
            </div>
        </section>
        <footer>SBTBS OTOBÜS BİLET SİSTEMLERİ</footer>
    </div>
    
    <!-- Delete Booking Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-circle"></i></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
            <h2 class="text-center pb-4">
                    Emin Misin?
            </h2>
            <p>
               Bileti Silmek İstediğinden Emin Misin? <strong>Bu işlem geri alınamaz.</strong>
            </p>
            <!-- Needed to pass pnr -->
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" id="delete-form"  method="POST">
                    <input id="delete-id" type="hidden" name="id">
                    <input id="delete-booked-seat" type="hidden" name="booked_seat">
                    <input id="delete-booked-bus" type="hidden" name="bus">
            </form>
      </div>
      <div class="modal-footer">
        <button type="submit" form="delete-form" class="btn btn-primary btn-danger" name="deleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>
     <!-- Option 1: Bootstrap Bundle with Popper -->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <!-- External JS -->
    <script src="assets/scripts/main.js"></script>
</body>
</html>