<?php

/**
 * UserService.php
 */

namespace App\Services;

class UserService {

    private $storage;
    private $isDBReady = true;

    /**
     * UserService constructor.
     */
    public function __construct() {
        // Verificación de la base de datos
        if ($this->isDBReady) {
            $this->storage = new StorageService();
        }
    }

    /**
     * Encargado de iniciar la sesión del usuario.
     *
     * @param string $email
     * @param string $password
     *
     * @return array
     */
    public function login($email, $password) {
        $result = [];

        // Verificamos que el email, sin espacios, tenga por lo menos 1 caracter
        if (strlen(trim($email)) > 0) {
            // Verificamos que el email tenga formato de email
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Verificamos que el password, sin espacios, tenga por lo menos 1 caracter
                if (strlen(trim($password)) > 0) {
                    // Si todo lo anterior tuvo éxito, iniciamos el query

                    // El query que vamos a ejecutar en la BD
                    $query = "SELECT id, email, full_name FROM usuarios WHERE email = :email AND password = :password LIMIT 1";

                    // Los parámetros de ese query
                    $params = [":email" => $email, ":password" => $password];

                    // Una vez que se cree la base de datos esté lista ésto se puede remover
                    if ($this->isDBReady) {
                        // El resultado de de ejecutar la sentencia se almacena en la variable `result`
                        $result = $this->storage->query($query, $params);

                        // Si la setencia tiene por lo menos una fila, quiere decir que encontramos a nuestro usuario
                        if (count($result['data']) > 0) {
                            // Almacenamos el usuario en la variable `user`
                            $user = $result['data'][0];

                            // Definimos nuestro mensaje de éxito
                            $result["message"] = "User found.";

                            // Enviamos de vuelta a quien consumió el servicio datos sobre el usuario solicitado
                            $result["user"] = [
                                "id" => $user["id"],
                                "email" => $user["email"],
                                "fullName" => $user["full_name"]
                            ];
                        } else {
                            // No encontramos un usuario con ese email y password
                            $result["message"] = "Invalid credentials.";
                            $result["error"] = true;
                        }
                    } else {
                        // La base de datos no está lista todavía
                        $result["message"] = "Database has not been setup yet.";
                        $result["error"] = true;
                    }
                } else {
                    // El password está en blanco
                    $result["message"] = "Password is required.";
                    $result["error"] = true;
                }
            } else {
                // El email no tiene formato de tal
                $result["message"] = "Email is invalid.";
                $result["error"] = true;
            }
        } else {
            // El email está en blanco
            $result["message"] = "Email is required.";
            $result["error"] = true;
        }

        return $result;
    }

    /**
     * Registra un nuevo usuario en el sistema.
     *
     * @param string $email
     * @param string $password
     * @param string $passwordConfirm
     * @param string $fullName
     *
     * @return array
     */
    public function register($email, $password, $passwordConfirm, $fullName) {
        $result = [];

        /**
         * TODO: Implementar
         * Pasos
         * - Verifique la existencia y validez de todos los datos, que todos existan y tengan el formato correcto,
         * use como guía el método `login`.
         * - Verifique que las contraseñas coincidan.
         * - Verifique que el email no ha sido usado en el sistema.
         * - Si todo lo anterior fue verificado existosamente, cree un nuevo usuario en el sistema y comuníquele a
         * quién consumió el servicio el resultado de la operación en forma de un array similar al del método `login`.
         */

        //Verifico que el email cumpla con las siguientes condiciones: sin espacios, al menos 1 caracter
        if (strlen(trim($email)) > 0) {
            //Verifico que tenga el formato de un correo
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                //Verifico que la contraseña cumpla con las siguientes condiciones: sin espacios, al menos 1 caracter
                if (strlen(trim($password)) > 0) {
                    /*
                        Verifico que la verificación de la contraseña cumpla con las siguientes condiciones: 
                        sin espacios, al menos 1 caracter, igual a la contraseña anterior
                    */
                    if (strlen(trim($passwordConfirm)) > 0 && $password == $passwordConfirm) {
                        //Si tuvo éxito, inicializo query para corrobar que los datos ingresados no existan
                        $query = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";

                        $params = [":email" => $email]; 
                        if ($this->isDBReady) {
                            $result = $this->storage->query($query, $params);
                            // Si la setencia tiene por lo menos una fila, quiere decir que encontramos a nuestro usuario
                            if (count($result['data']) > 0) {
                                // Definimos nuestro mensaje de error
                                $result["message"] = "The user has already been registered. Try with another one.";
                                $result["error"] = true;
                            } else {
                                //Inicializo query para crear el usuario registrado
                                $query = "INSERT INTO usuarios VALUES (NULL, ':email', ':password', ':fullName')";

                                $params = [":email" => $email, ":password" => $password, "fullName" => $fullName];
                                if ($this->isDBReady) {
                                    $result = $this->storage->query($query, $params);
                                    // Almacenamos el usuario en la variable `user`
                                    $user = $result['data'][0];

                                    $result["message"] = "The user has been succesfully registered.";

                                    // Enviamos de vuelta a quien consumió el servicio datos sobre el usuario solicitado
                                    $result["user"] = [
                                        "email" => $user["email"],
                                        "fullName" => $user["full_name"]
                                    ];
                                } else {
                                    $result["message"] = "The user couldn't be registered. ";
                                    $result["error"] = true;
                                }
                            }
                        } else {
                            $result["message"] = "Database has not been setup yet.";
                            $result["error"] = true;
                        }// buscar usuario
                    } else {
                        $result["message"] = "Passwords don't match.";
                        $result["error"] = true;
                    }// confirmación password
                } else {
                    $result["message"] = "Password is required.";
                    $result["error"] = true;
                }//password
            } else {
                $result["message"] = "Email is invalid";
                $result["error"] = true;
            } //formato email
        } else {
            $result["message"] = "Email is required.";
            $result["error"] = true;
        } //email

        return $result;
    }

}
