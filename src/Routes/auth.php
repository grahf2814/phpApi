<?php
use App\Config\Security;

echo json_encode(Security::secretKey());

