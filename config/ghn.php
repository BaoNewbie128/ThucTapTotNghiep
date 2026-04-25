<?php
header('Content-Type: application/json; charset=utf-8');

define('GHN_TOKEN', getenv('GHN_TOKEN') ?: '');
define('GHN_SHOP_ID', getenv('GHN_SHOP_ID') ?: '');
define('GHN_BASE_URL', getenv('GHN_BASE_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/');

class GHN_API {
    private $token;
    private $shop_id;
    private $base_url;

    public function __construct() {
        $this->token = GHN_TOKEN;
        $this->shop_id = GHN_SHOP_ID;
        $this->base_url = GHN_BASE_URL;
    }

    private function makeRequest($endpoint, $data = [],$method = 'POST') {
        if ($this->token === '' || $this->shop_id === '') {
            return ['error' => 'Missing GHN_TOKEN or GHN_SHOP_ID environment configuration'];
        }

        $url = $this->base_url . $endpoint;
        if($method == 'GET' && !empty($data)){
            $url .= '?' . http_build_query($data);
        }
        $headers = [
            'Content-Type: application/json',
            'Token: ' . $this->token,
            'ShopId: ' . $this->shop_id
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if($method == 'POST'){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => 'GHN cURL error', 'message' => $error];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['error' => 'Invalid JSON response from GHN', 'http_code' => $httpCode];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $decoded['http_code'] = $httpCode;
            $decoded['error'] = $decoded['message'] ?? 'GHN HTTP error';
        }

        return $decoded;
    }

    public function calculateFee($from_district_id, $to_district_id, $to_ward_code, $weight = 500, $service_id = null) {
        $data = [
            'from_district_id' => $from_district_id,
            'to_district_id' => $to_district_id,
            'to_ward_code' => $to_ward_code,
            'weight' => $weight,
            'service_id' => $service_id
        ];

        return $this->makeRequest('shipping-order/fee', $data);
    }

    public function getProvinces() {
        return $this->makeRequest('master-data/province', [], 'GET');
    }

    public function getDistricts($province_id) {
        if (!filter_var($province_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            return ['error' => 'province_id must be a positive integer'];
        }

        return $this->makeRequest('master-data/district', ['province_id' => $province_id], 'GET');
    }

    public function getWards($district_id) {
        if (!filter_var($district_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            return ['error' => 'district_id must be a positive integer'];
        }

        return $this->makeRequest('master-data/ward', ['district_id' => $district_id], 'GET');
    }

    public function createOrder($order_data) {
        return $this->makeRequest('shipping-order/create', $order_data);
    }
}
$api = new GHN_API();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'provinces':
        echo json_encode($api->getProvinces());
        break;

    case 'districts':
        $province_id = $_GET['province_id'] ?? 0;
        echo json_encode($api->getDistricts($province_id));
        break;

    case 'wards':
        $district_id = $_GET['district_id'] ?? 0;
        echo json_encode($api->getWards($district_id));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>