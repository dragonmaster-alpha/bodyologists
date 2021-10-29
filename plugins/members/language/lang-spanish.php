<?php

/**
 * @author
 * Web Design Enterprise
 * Phone: 786.234.6361
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * � 2002-2009 Web Design Enterprise Corp. All rights reserved.
 */
 
global $sitename;

# registration page
define("_MEMBERS_REGISTRATION", "Registro de clientes");
define("_MEMBERS_REGISTRATION_HEAD", "Informaci&oacute;n del cliente");
define("_MEMBERS_REGISTRATION_BLABLA", "<b>Nota:</b> Todos los campos son requeridos");
define("_MEMBERS_REGISTRATION_PERSONAL_DETAILS", "Informaci&oacute;n Personal");
define("_MEMBERS_REGISTRATION_GENDER", "Genero");
define("_MEMBERS_REGISTRATION_DOB", "Fecha de nacimiento");
define("_MEMBERS_REGISTRATION_NAME", "Nombre completo");
define("_MEMBERS_REGISTRATION_COMPANY", "Empresa");
define("_MEMBERS_REGISTRATION_ADDRESS", "Direcci&oacute;n");
define("_MEMBERS_REGISTRATION_CITY", "Ciudad");
define("_MEMBERS_REGISTRATION_STATE", "Provincia");
define("_MEMBERS_REGISTRATION_ZIPCODE", "C&oacute;digo postal");
define("_MEMBERS_REGISTRATION_PHONE_NUMBER", "Tel&eacute;fono");
define("_MEMBERS_REGISTRATION_FAX_NUMBER", "Fax");
define("_MEMBERS_REGISTRATION_LOGIN_DETAILS", "Identificaci&oacute;n");
define("_MEMBERS_REGISTRATION_EMAIL", "Email");
define("_MEMBERS_REGISTRATION_PASSWORD", "Crear una contrase&ntilde;a");
define("_MEMBERS_REGISTRATION_PASSWORD_CONFIRM", "Confirmar contrase&ntilde;a");
define("_MEMBERS_NEWSLETTERS_SUSCRIBE", "Quiere subscribirse a nuestros newsletters?");
define("_MEMBERS_REGISTRATION_SECURITY_CHECK", "Seguridad");
define("_MEMBERS_REGISTRATION_SECURITY_CHECK_EXP", "<b>Chequeo de seguridad</b><br /><br />Este es un procedimiento est&aacute;ndar para verificar su identidad.");
define("_MEMBERS_REGISTRATION_I_AGREE", "He le&iacute;do y estoy de acuerdo con los <a href=\"terms.html\" title=\"_blank\">T&eacute;rminos y condisiones</a> de este sitio.");
define("_MEMBERS_REGISTRATION_DONE", "<span style=\"font-size: 12px;\">Su registro a sido creado, un email a sido enviado a usted con toda su informaci&oacute;n de registro.<br /><br />Si tiene alguna pregunta por favor cont&aacute;ctenos, es un placer para nosotros poderlo ayudar en lo que necesite.</span>");
define("_MEMBERS_REGISTRATION_TO_VALIDATE", "<span style=\"font-size: 12px;\">Your data has been successfully recorded and an email will be sent to you within the next 5 minutes with instructions of how to validate your account<br /><br />If you have any question and would like to contact us please call us or use our <a href=\"?plugin=Contact\">contact form</a><br /><br />Thank you.");
define("_MEMBERS_REGISTRATION_TYPE_TWICE", "entre su nueva contrase&ntilde;a");
define("_MEMBERS_REGISTRATION_PAGE_TITLE", "Registro de miembros");
define("_MEMBERS_REGISTRATION_DUPLICATED_GROUP", "Lo sentimos pero el grupo seleccionado ya existe, trate otro");
define("_MEMBERS_REGISTRATION_EMPTY_GROUP", "El nombre del grupo no puede estar vacio.");

#Registration Errors
define("_MEMBERS_REGISTRATION_ERROR_FILL_ALL", "Por favor, rellene todos los campos");
define("_MEMBERS_REGISTRATION_ERROR_INCORRECT_EMAIL", "La direcci&oacute;n email esta incorrecta");
define("_MEMBERS_REGISTRATION_ERROR_DUPLICATED_EMAIL", "Su direcci&oacute;n email ya existe en este sitio, trate otra");
define("_MEMBERS_REGISTRATION_ERROR_MUST_AGREE", "Debe estar de acuerdo con nuestros t&eacute;rminos y condiciones antes de proseguir.");
define("_MEMBERS_REGISTRATION_ERROR_INCORRECT_GENDER", "Por favor escoja su genero.");
define("_MEMBERS_REGISTRATION_ERROR_INCORRECT_DOB", "Por favor enter su fecha de nacimiento.");
define("_MEMBERS_REGISTRATION_ERROR_INCORRECT_COMPANY", "Por favor entre el nombre de su empresa.");
define("_MEMBERS_REGISTRATION_ERROR_INCORRECT_FAX", "Por favor entre su numero de fax.");
define("_MEMBERS_REGISTRATION_ERROR_YOU_MUST_AGREE", "Usted debe aceptar nuestros terminos y condisiones antes de continuar.");
define("_MEMBERS_REGISTRATION_ERROR_NO_ADULT", "Lo sentimos pero usted debe tener 18 al menos a&ntilde;os para poder registrarse en nuestro website..");

# Activation
define("_MEMBERS_ACTIVATION_MISSING_INFO", "Informaci&oacute;n perdida");
define("_MEMBERS_ACTIVATION_MISSING_INFO_TEXT", "There is some missed information in the request you just did, please try registering again using a different email address.<br /><br />Sorry for the inconveniences.");
define("_MEMBERS_ACTIVATION_NO_MATCH_INFO", "Info do not match");
define("_MEMBERS_ACTIVATION_NO_MATCH_INFO_TEXT", "The submitted information didn�t match any of our records; please try register again using a different email address.<br /><br />Sorry for the inconveniences.");
define("_MEMBERS_ACTIVATION_PAGE_TITLE", "Activation de miembros");
define("_MEMBERS_ACTIVATION_DONE_TEXT", "Your account has been confirmed and activated; from now on you can login to your account using your email and your selected password.<br /><br />Welcome to our community and thank you for your support.");

# Main page
define("_MEMBERS_MAIN_MY_ACCOUNT", "Mi cuenta");
define("_MEMBERS_MAIN_PAGE_WELCOME", "Bienvenido");
define("_MEMBERS_MAIN_TEXT", "");
define("_MEMBERS_MAIN_ADDRESS_BOOK", "Libro de direcciones");
define("_MEMBERS_MAIN_EDIT_PROFILE", "Editar su perfil");
define("_MEMBERS_MAIN_VIEW_ORDERS", "Todas sus ordenes");
define("_MEMBERS_MAIN_LASTEST_ORDERS", "Ultimas ordenes");
define("_MEMBERS_MAIN_TRANSACTIONS", "Transacci&oacute;n");
define("_MEMBERS_MAIN_TOTAL", "Total");
define("_MEMBERS_MAIN_STATUS", "Estado");
define("_MEMBERS_MAIN_DATE", "Fecha");
define("_MEMBERS_MAIN_TRACKING_NUMBER", "Numero  #");
define("_MEMBERS_MAIN_NO_ORDERS_YET", "No tenemos ning&uacute;n record de ordenes previas.");

# Login Page
define("_MEMBERS_LOGIN_PAGE_TITLE", "Entrar o registrarse");
define("_MEMBERS_LOGIN_PAGE_UNKNOWN_EMAIL", "Email desconocido. Por favor verifique e intente nuevamente.");
define("_MEMBERS_LOGIN_PAGE_USERORPASSWD_INCORRECT", "Email y contrase&ntilde;a no est&aacute;n correctas, trate nuevamente.");
define("_MEMBERS_LOGIN_PAGE_HEADER_TEXT", "Entrar o registrarse");
define("_MEMBERS_LOGIN_PAGE_LOGIN_HEAD", "Ya es un miembro, inicie sesi&oacute;n.");
define("_MEMBERS_LOGIN_PAGE_LOGIN_COMMENT", "Si ya es uno de nuestros miembros por favor inicie su sesi&oacute;n.");
define("_MEMBERS_LOGIN_PAGE_REGISTRATION", "Nuevo por ac&aacute;? Reg&iacute;strese ahora");
define("_MEMBERS_LOGIN_PAGE_REGISTRATION_COMMENT", "La registraci&oacute;n le tomara un minuto. Reg&iacute;strese ahora y comience a disfrutar de todos los beneficios por ser uno m&aacute;s de nuestra comunidad.");
define("_MEMBERS_LOGIN_PAGE_USERNAME", "Email");
define("_MEMBERS_LOGIN_PAGE_PASSWORD", "contrase&ntilde;a");
define("_MEMBERS_LOGIN_PAGE_FORGOT_PASSWORD", "Perdi&oacute; su <a href=\"index.php?plugin=Members&amp;op=lostPasswd\">contrase&ntilde;a?</a>");
define("_MEMBERS_REMEMBER_ME", "Recordarme");

# Logout
define("_MEMBER_LOGGED_OUT_HEADER", "Usted ha cerrado su sesi&oacute;n");
define("_MEMBER_LOGGED_OUT", "Usted ha cerrado su sesi&oacute;n. <a href=\"index.php?plugin=Members\">haga clic aqu&iacute;</a> Para iniciarla nuevamente.<br /><br />Si tiene preguntas o dudas por favor cont&aacute;ctenos.");

# Forgoten Password
define("_MEMBERS_FORGOTTEN_PAGE_TITLE", "Recuperar su contrase&ntilde;a");
define("_MEMBERS_FORGOTTEN_PASS", "Problemas asesando su cuenta?");
define("_MEMBERS_FORGOTTEN_PASS_TEXT", "Entre su email. Nosotros le enviaremos un email con informaci&oacute;n de c&oacute;mo recuperar su cuenta.");
define("_MEMBERS_FORGOTTEN_PASS_ERROR", "El email suministrado no est&aacute; en nuestra base de datos.");
define("_MEMBERS_FORGOTTEN_PASS_LINK_ERROR", "El enlace que usted ha seguido est&aacute; incompleto o err&oacute;neo.");
define("_MEMBERS_FORGOTTEN_PASS_DONOT_MATCH", "La informaci&oacute;n enviada no aparece en nuestros records");
define("_MEMBERS_FORGOTTEN_MORE_HELP", "Necesita m&aacute;s ayuda? Cont&aacute;ctenos ahora para darle una mano.");
define("_MEMBERS_FORGOTTEN_SENT_PAGE_TITLE", "contrase&ntilde;a ");
define("_MEMBERS_FORGOTTEN_PASS_SENT_HEADER", "Su contrase&ntilde;a ha sido restablecida");
define("_MEMBERS_FORGOTTEN_PASS_SENT", "Un email ha sido enviado a la direcci&oacute;n suministrada. Este email describe como recuperar su contrase&ntilde; a.<br /><br />Please be patient; the delivery of email may be delayed. Remember to check your junk or spam folder or filter if you do not receive this email.");
define("_MEMBERS_FORGOTTEN_JUST_RESET", "");

# Reset password
define("_MEMBERS_PASSWDRESET_HEADER", "Cambiar contrase&ntilde;a");
define("_MEMBERS_PASSWDRESET_PAGE_TITLE", "Cambiar contrase&ntilde;a");
define("_MEMBERS_PASSWDRESET", "Entre su nueva contrase&ntilde;a.");
define("_MEMBERS_PASSWDRESET_NEW_PASSWD", "Nueva contrase&ntilde;a");
define("_MEMBERS_PASSWDRESET_CONFIRM_PASSWD", "Confirmar contrase&ntilde;a");
define("_MEMBERS_PASSWDRESET_ERROR", "contrase&ntilde;a y confirmaci&oacute;n no son iguales");
define("_MEMBERS_PASSWDRESET_LINKS_ERROR", "Ha habido un error, informaci&oacute;n invalida o insuficiente.");

# Address book
define("_MEMBERS_ADDRESS_PAGE_TITLE", "&Aacute;rea de miembros - Libro de direcciones");
define("_MEMBERS_ADDRESS_HEADER_TEXT", "Libro de direcciones");
define("_MEMBERS_ADDRESS_NEW_DEFAULT", "defecto");
define("_MEMBERS_ADDRESS_ADD_ADDRESS", "agregar direcci&oacute;n");
define("_MEMBERS_ADDRESS_EDIT", "editar");
define("_MEMBERS_ADDRESS_DELETE", "borrar");
define("_MEMBERS_ADDRESS_TEXT", "");
define("_MEMBERS_ADDRESS_EDIT_ADDRESS_HEADER", "Editando direcci&oacute;n");
define("_MEMBERS_ADDRESS_ADD_ADDRESS_HEADER", "Agregando nueva direcci&oacute;n");
define("_MEMBERS_ADDRESS_MAKE_DEFAULT", "Has esta mi direcci&oacute;n por defecto.");
define("_MEMBERS_ADDRESS_NEED_ADDRESSES", "Lo sentimos, lo que trata de hacer es invalido, necesitamos al menos una direcci&oacute;n en su libro de direcciones.");
define("_CONFIRM_ADDRESS_DELETION", "Confirme el borrado de esta direcci&oacute;n");

# Edit Profile
define("_MEMBERS_EDITING_PROFILE", "Editando Perfil");
define("_MEMBERS_EDITING_PROFILE_TEXT", "Use el siguiente formulario para hacer cambios a su perfil.");
define("_MEMBERS_NEED_MORE_HELP", "<br />Necesita ayuda? Cont&aacute;ctenos!");
define("_MEMBERS_SAME_PROFILE_ADDRESS", "This must be the same address that you entered in your profile.");
define("_MEMBERS_PASS_RECOVERY_SENT", "Su contrase&ntilde;a ha sido enviada. Una vez la tenga use el siguiente formulario para iniciar sesion.");
define("_MEMBERS_PASS_BY_EMAIL_TEXT", "This email is answering your password forgotten request, if you did not made this request don't worry about it, nobody else can read this email.\n\nFor security reazons we had replaced your password with the following temporal one.\n\nPassword:");
define("_MEMBERS_PASS_RECOVERY_SUBJECT", "Recobrar contrase&ntilde;a");
define("_MEMBERS_SUCCESS_UPDATED", "Su informaci&oacute;n ha sido correctamente cambiada.");
define("_MEMBERS_SUCCESS_ERROR", "Ha habido un error actualizando su informaci&oacute;n.");
define("_MEMBERS_UDATING_DATA", "Actualizando informaci&oacute;n");
define("_MEMBERS_PASSWDS_DO_NOT_MATCH", "Contrase&ntilde;a y confirmaci&oacute;n no son iguales");
define("_MEMBERS_DUPLICATED_ADDRESS_TRY_OTHER", "Email duplicado, trate otro");
define("_MEMBERS_EDIT_PROFILE_HEADER_TEXT", "Editando Perfil");

# Orders
define("_MEMBERS_ORDERS_OVERVEW", "Ordenes");
define("_MEMBERS_ORDERS_REMAKE", "Reordenar");
define("_MEMBERS_ORDERS_STATUS_PENDING", "Procesando");
define("_MEMBERS_ORDERS_STATUS_SHIPPED", "Enviado");
define("_MEMBERS_ORDERS_ITEM_NOT_EXISTS", "Lo sentimos, No hay informaci&oacute;n acerca de la orden que trata de ver");
define("_MEMBERS_ORDERS_MADE_ORDERS", "Listado de &oacute;rdenes");
define("_MEMBERS_ORDERS_OVERVEW_PRINT_ORDER", "Imprimir");
define("_MEMBERS_ORDERS_OVERVEW_DATE_TIME", "Fecha");
define("_MEMBERS_ORDERS_OVERVEW_SHIP_TO", "Enviar a");
define("_MEMBERS_ORDERS_OVERVEW_EDIT", "editar");
define("_MEMBERS_ORDERS_BILL_TO", "Cobrado a");
define("_MEMBERS_ORDERS_OVERVEW_EMAIL_RECEIPT_TO", "Email enviado a");
define("_MEMBERS_ORDERS_OVERVEW_PAY_WITH", "Pagado con");
define("_MEMBERS_ORDERS_CARD_EXPIRES", "Expira");
define("_MEMBERS_ORDERS_OVERVEW_ITEMS", "Productos ordenados");
define("_MEMBERS_ORDERS_QTY", "Cantidad");
define("_MEMBERS_ORDERS_PRICE", "Precio");
define("_MEMBERS_ORDERS_PRODUCT_ITEM", "art.no.");
define("_MEMBERS_ORDERS_SUBTOTAL", "Sub-total");
define("_MEMBERS_ORDERS_SHIPPING", "Manejo y env&iacute;o");
define("_MEMBERS_ORDERS_TAXES", "Impuesto");
define("_MEMBERS_ORDERS_PROMO_COUPON", "Cup&oacute;n");
define("_MEMBERS_ORDERS_COUPON_SAVED", "Ahorrado");
define("_MEMBERS_ORDERS_TOTAL", "Total");
define("_ORDER_STATUS", "Estado de la orden");
define("_MEMBERS_ORDERS_TRANS_NO", "No de Trasaci&oacute;n:");

# Nav Menu
define("_ADDRESSES", "Direcciones");
define("_WISHLIST", "Lista de deseos");

# Packages
define("_PACKAGE_SELECT_PACKAGE_BLABLA", "Use el siguiente formulario para hacer su pago.");
define("_PACKAGE_PAYMENT_INFO", "Targeta de credito");

# Emails
define("_MEMBERS_EMAIL_REGISTRATION_WELCOME	", "Welcome to");
define("_MEMBERS_EMAIL_REGISTRATION_SUBJECT", "Account details for #name from #sitename");
define("_MEMBERS_EMAIL_REGISTRATION_TEXT_TOP", "Hello");
define("_MEMBERS_EMAIL_REGISTRATION_BODY_TOP", "Thank you for registering on our website. You may now log in to your account at:<br /><br />");
define("_MEMBERS_EMAIL_REGISTRATION_BODY_MIDDLE", "The email address used to register in our website should be also used to log into your account, you can also update your account profile on the following address:");
define("_MEMBERS_EMAIL_REGISTRATION_BODY_BOTTOM", "Once again, thank you very much for using our services.");
define("_MEMBERS_EMAIL_REGISTRATION_BODY_SOCIAL", "You can also find us on the following places:");

define("_MEMBERS_EMAIL_VALIDATION_WELCOME	", "Welcome to");
define("_MEMBERS_EMAIL_VALIDATION_SUBJECT", "Validation Required from #sitename");
define("_MEMBERS_EMAIL_VALIDATION_TEXT_TOP", "Hello");
define("_MEMBERS_EMAIL_VALIDATION_BODY_TOP", "Thank you for registering on our website. All you need to do now is click on the following link so we can validate your email address:<br /><br />");
define("_MEMBERS_EMAIL_VALIDATION_BODY_MIDDLE", "Once this validation is completed you can access your account and start enjoying the benefits of being part of our community");
define("_MEMBERS_EMAIL_VALIDATION_BODY_BOTTOM", "Once again, thank you very much for using our services.");
define("_MEMBERS_EMAIL_VALIDATION_BODY_SOCIAL", "You can also find us on the following places:");

define("_MEMBERS_EMAIL_LOST_PASSWORD_SUBJECT", "Password Recovery Request from #sitename");
define("_MEMBERS_EMAIL_LOST_PASSWORD_TEXT_TOP", "Hi");
define("_MEMBERS_EMAIL_LOST_PASSWORD_BODY_TOP", "You recently asked to reset your password on our website, please click on the following link to preceed with your request:");
define("_MEMBERS_EMAIL_LOST_PASSWORD_BODY_MIDDLE", "Didn't request this change?");
define("_MEMBERS_EMAIL_LOST_PASSWORD_BODY_BOTTOM", "If you did not reset your password, please disregard this message. Nothing will change in your account");
define("_MEMBERS_EMAIL_VALIDATION_BODY_SOCIAL", "Remembers we are also available for you on the following places:");
