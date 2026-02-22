<?php

namespace app\Controllers;

use config\DBConnection;
use app\Models\SyncModel;

class SyncController
{
    private $syncModel;

    public function __construct()
    {
        $db = new DBConnection();
        $this->syncModel = new SyncModel($db);
    }

    public function handleAutoSync()
    {
        // 1. Push local transactions to remote
        $pushResults = $this->syncModel->pushLocalData();

        // 2. Pull remote products/data to local DB
        $pullResults = $this->syncModel->pullRemoteData();

        // 3. Download physical images if they don't exist locally yet
        $imageSyncCount = $this->syncImages();

        $results = [
            'push' => $pushResults,
            'pull' => $pullResults,
            'images_downloaded' => $imageSyncCount // Added to response for debugging
        ];

        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    public function syncImages()
    {
        $remoteUrlBase = "https://admin.lugodsquare.com/uploads/products/";

        // Path adjusted for typical XAMPP structure: htdocs/your_project/public/uploads/products/
        $localPathBase = $_SERVER['DOCUMENT_ROOT'] . "/public/uploads/products/";

        if (!is_dir($localPathBase)) {
            mkdir($localPathBase, 0755, true);
        }

        // Get products from your model (make sure this method exists in SyncModel)
        // If your SyncModel doesn't have this, you might use $this->syncModel->getLocalProducts()
        $products = $this->syncModel->getAllLocalProducts();

        $downloadCount = 0;

        if ($products) {
            foreach ($products as $product) {
                $imageName = $product['product_image'];

                if (empty($imageName)) continue;

                $localFile = $localPathBase . $imageName;
                $remoteFile = $remoteUrlBase . $imageName;

                if (!file_exists($localFile)) {
                    $imageContent = @file_get_contents($remoteFile);
                    if ($imageContent !== false) {
                        file_put_contents($localFile, $imageContent);
                        $downloadCount++;
                    }
                }
            }
        }

        return $downloadCount;
    }

    public function pullAndPush()
    {
        return $this->handleAutoSync();
    }
}
