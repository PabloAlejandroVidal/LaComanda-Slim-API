<?php
namespace App\Services;

class Utils{
    const LOG_DIR = './Logs/';
    public static function saveImage($imagen, $dir, $file){
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $imagen->moveTo($dir . $file);
    }
    public static function checkImage($imagen){
        return ($imagen !== null && $imagen->getError() === UPLOAD_ERR_OK);
    }
    public static function getExtension($imagen){
        return pathinfo($imagen->getClientFilename(), PATHINFO_EXTENSION);
    }
    public static function saveLog($logFileName, $logMessage) {
        $logFileName = $logFileName . '.log';
        $logFilePath = self::LOG_DIR . $logFileName;
    
        if (!is_dir(dirname($logFilePath))) {
            mkdir(dirname($logFilePath), 0777, true);
        }
    
        return file_put_contents($logFilePath, $logMessage . PHP_EOL, FILE_APPEND);
    
    }
    
    public static function getLog($logFileName)
    {
        $logFileName = $logFileName . '.log';
        $logFilePath = self::LOG_DIR . $logFileName;
        
        // Obtener las líneas guardadas en el archivo
        $logLines = file($logFilePath, FILE_IGNORE_NEW_LINES);
        
        return $logLines;
    }

    public static function generarCodigoAlfanumerico($longitud) {
        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codigo = '';
        
        $caracteresLength = strlen($caracteres);
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[rand(0, $caracteresLength - 1)];
        }
        
        return $codigo;
    }
    
    public static function getHoraActual(){
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        return date('Y-m-d H:i:s');
    }
}
?>