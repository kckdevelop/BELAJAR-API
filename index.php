<?php
header('Content-Type: application/json');
include_once "db.php";


// Ambil path dan method dari request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

// Routing sederhana
if ($request[0] == 'products') {
  switch ($method) {
      case 'GET':
          if (isset($request[1])) {
              // GET /products/{id}
            $id = (int)$request[1];
            $stmt = $conn->prepare("SELECT * FROM product WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            echo json_encode($product ?: ["error" => "Produk tidak ditemukan"]);
          } else {
              // GET /products
            $result = $conn->query("SELECT * FROM product");
            $products = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($products);
          }
          break;

      case 'POST':
        //update
        if (isset($request[1])) {
            $id = (int)$request[1];
            $stmt = $conn->prepare("SELECT * FROM product WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            

            if($result)
            {
                $name = $_POST['name'] ?? null;
                $price = $_POST['price'] ?? null;

                if (!$name || !$price) {
                    echo json_encode(["error" => "Nama dan harga wajib diisi"]);
                    exit;
                }
                $stmt = $conn->prepare("UPDATE product SET name = ?, price = ? WHERE id = ?");
                $stmt->bind_param("sii", $name, $price, $id);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    echo json_encode([
                        "message" => "Produk berhasil diperbarui",
                        "product" => [
                            "id" => $id,
                            "name" => $name,
                            "price" => $price
                        ]
                    ]);
                } else {
                    echo json_encode(["error" => "Produk tidak ditemukan atau tidak ada perubahan"]);
                }
            }
            else
            {
                echo json_encode($product ?: ["error" => "Produk tidak ditemukan"]);
            }

        }
        else
        {
          // Tambah Data
              $name = $_POST['name'] ?? null;
              $price = $_POST['price'] ?? null;

          if (!$name || !$price) {
              echo json_encode(["error" => "Nama dan harga harus diisi"]);
              exit;
          }

          $stmt = $conn->prepare("INSERT INTO product (name, price) VALUES (?, ?)");
                    $stmt->bind_param("si", $name, $price);
                    $success = $stmt->execute();

              if ($success) {
                  echo json_encode([
                      "message" => "Produk ditambahkan",
                      "product" => [
                          "id" => $stmt->insert_id,
                          "name" => $name,
                          "price" => $price
                      ]
                  ]);
              } else {
                  echo json_encode(["error" => "Gagal menambahkan produk"]);
              }
            }
          break;

    //update
      case 'PUT':
        if (isset($request[1])) {
            $id = (int)$request[1];
            $input = json_decode(file_get_contents("php://input"), true);
    
            $name = $input['name'] ?? null;
            $price = $input['price'] ?? null;
    
            if (!$name || !$price) {
                echo json_encode(["error" => "Nama dan harga harus diisi"]);
                exit;
            }
    
            $stmt = $conn->prepare("UPDATE product SET name = ?, price = ? WHERE id = ?");
            $stmt->bind_param("sii", $name, $price, $id);
            $stmt->execute();
    
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    "message" => "Produk berhasil diperbarui",
                    "product" => [
                        "id" => $id,
                        "name" => $name,
                        "price" => $price
                    ]
                ]);
            } else {
                echo json_encode(["error" => "Produk tidak ditemukan atau tidak ada perubahan"]);
            }
        } else {
            echo json_encode(["error" => "ID produk tidak ditemukan di URL"]);
        }
      break;

      case 'DELETE':
        if (isset($request[1])) {
          $id = (int)$request[1];
  
          $stmt = $conn->prepare("DELETE FROM product WHERE id = ?");
          $stmt->bind_param("i", $id);
          $stmt->execute();
  
          if ($stmt->affected_rows > 0) {
              echo json_encode(["message" => "Produk dengan ID $id berhasil dihapus"]);
          } else {
              echo json_encode(["error" => "Produk tidak ditemukan"]);
          }
      } else {
          echo json_encode(["error" => "ID produk harus disertakan di URL"]);
      }
      break;

      
      default:
          echo json_encode(["error" => "Metode tidak didukung"]);
  }
} else {
  echo json_encode(["error" => "Endpoint tidak ditemukan"]);
}

?>
