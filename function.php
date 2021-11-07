<?php 
    // koneksi ke database
    $conn = mysqli_connect("localhost","root","110688","phpdasar");

    function query ($query){
        global $conn;
        $result = mysqli_query($conn,$query);
        $rows = [];
        while($row = mysqli_fetch_assoc($result)){
            $rows[] = $row;
        };
        return $rows;
    }

    function tambah($data){
        global $conn;
        $nrp = htmlspecialchars($data["nrp"]); 
        $nama = htmlspecialchars ( $data["Nama"]);
        $email = htmlspecialchars ($data["email"]);
        $jurusan =htmlspecialchars ($data["jurusan"]);
        //upload gambar
        $gambar = upload(); 

        if (!$gambar) {
            return false;
        }

        $query = "INSERT INTO mahasiswa VALUES (0,'$nama', '$nrp', '$email', '$jurusan', '$gambar')";
        mysqli_query($conn,$query);

        return mysqli_affected_rows($conn);
    }

    function upload(){
        $nameFile = $_FILES['gambar']['name'];
        $ukuranFile = $_FILES['gambar']['size'];
        $error = $_FILES['gambar']['error'];
        $tmpName = $_FILES['gambar']['tmp_name'];

        //cek apakah tidak ada gambar yang diupload
        if ($error === 4) {
            echo "<script>
                    alert('tambahkan gambar terlebih dahulu');
                    </script>";
            return false;
        }

        //cek apakah yang diupload adalah gambar
        $ekstensiGambarValid = ['jpg','jpeg','png'];
        $ekstensiGambar = explode('.', $nameFile);
        $ekstensiGambar = strtolower(end($ekstensiGambar));
        if (!in_array($ekstensiGambar,$ekstensiGambarValid)) {
            echo "<script>
            alert('yang diupload bukan gambar');
            </script>";
            
            return false;
        }

        //cek jika ukurannnya terlalu besar
      if ($ukuranFile > 1000000) {
        echo "<script>
        alert('Ukuran gambar terlalu besar bos!!');
        </script>";
        
        return false;
      }
      //lolos pengecekan, gambar siap diupload
      //generate nama baru gambar
      $namaFileBaru = uniqid();
      $namaFileBaru .= '.';
      $namaFileBaru .= $ekstensiGambar;
      move_uploaded_file($tmpName,'fotoanime/'.$namaFileBaru);

      return $namaFileBaru;
    }

    function hapus($id){
        global $conn;
        mysqli_query($conn,"DELETE FROM mahasiswa WHERE id = $id");

        return mysqli_affected_rows($conn);
    }

    function ubah($data){
        global $conn;
        $id = $data["id"];
        $nrp = htmlspecialchars($data["nrp"]); 
        $nama = htmlspecialchars ( $data["Nama"]);
        $email = htmlspecialchars ($data["email"]);
        $jurusan =htmlspecialchars ($data["jurusan"]);
        $gambarLama = $data["gambarLama"];
        //cek apakah user memilih gambar baru / tidak
        if ($_FILES['gambar']['error'] === 4) {
            $gambar = $gambarLama;
        } else {
            $gambar = upload();
        }

        $query = "UPDATE mahasiswa SET 
                    Nama = '$nama',
                    nrp = '$nrp',
                    jurusan = '$jurusan',
                    gambar = '$gambar'
                    WHERE id = $id ";
        mysqli_query($conn,$query);

        return mysqli_affected_rows($conn);
    }

    function cari($keyword){
        $query = "SELECT * FROM mahasiswa 
        WHERE
         nrp LIKE '%$keyword%' OR
         Nama LIKE '%$keyword%' OR
         email LIKE '%$keyword%' OR
         jurusan LIKE '%$keyword%'
         ";
        return query($query);
    }

    function registrasi($data){
        //lakukan koneksi ke databases
        global $conn;

        //kita tangkap dulu value $_POSTnya dari form registrasi dan kita masukan ke dalam variabel
        //stripslashes bertujuan untuk membersihkan karakter dari karaktek back splas
        $username = strtolower(stripslashes($data["username"]));

        // mysqli_real_escape_string bertujuan untuk menambahkan tanda kutip pada password di database
        $password = mysqli_real_escape_string($conn,$data["password"]);
        $password2 = mysqli_real_escape_string($conn,$data["password2"]);

        //cek username sudah ada atau belum
        //lakukan query pada tabel untuk mengetahui ada/tidak usernamenya dan simpan di variabel
        $result = mysqli_query($conn,"SELECT username FROM users WHERE username = '$username'");

        //lakukan pengecekan menggunakan mysqli_fetch_assoc() untuk memberikan nilai true jika ada username didalam databases
        if (mysqli_fetch_assoc($result)) {
            echo "<script>
                alert('username sudah ada');
                </script>";
        
        return false;
        }

        //cek konfirmasi password
        if ($password !== $password2) {
            echo "<script>
                alert('Konfirmasi password tidak sesuai');
                </script>";
        
        return false;
        }

        //enkripsi passwordnya menggunakan function password_hash
        //parameter pertama diisi password apa yang mau diacak
        //parameter kedua diisi algoritma apa yang mau dipakai untuk mengacak password
        //PASSWORD_DEFAULT adalah algoritma yang dipilih secara default oleh php, algoritma ini akan terus berubah ketika ada cara pengamanan baru
        $password = password_hash($password,PASSWORD_DEFAULT);

        //tambahkan user baru kedatabase
        $query = "INSERT INTO users VALUES (0,'$username','$password')";
        mysqli_query($conn,$query);

        return mysqli_affected_rows($conn);
    }
?>
