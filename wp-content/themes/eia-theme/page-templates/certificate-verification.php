<?php
/**
 * Template Name: Vérification Certificat
 *
 * @package EIA_Theme
 */

show_admin_bar(true);

get_header();

$certificate_code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';
$cert = null;

if ($certificate_code && class_exists('EIA_Certificates')) {
    $certificates = EIA_Certificates::get_instance();
    $cert = $certificates->get_certificate_by_code($certificate_code);
}
?>

<style>
body.admin-bar {
    margin-top: 32px !important;
}
#wpadminbar {
    display: block !important;
    position: fixed !important;
    top: 0 !important;
    z-index: 99999 !important;
}
@media print {
    /* Masquer tous les éléments sauf le certificat */
    body > *:not(.eia-certificate-container) {
        display: none !important;
    }
    #wpadminbar,
    .no-print,
    header,
    footer,
    .site-header,
    .site-footer,
    nav,
    .navigation,
    #masthead,
    #colophon {
        display: none !important;
    }
    body {
        margin: 0 !important;
        padding: 0 !important;
    }
    .eia-certificate-container {
        margin: 0 !important;
        padding: 0 !important;
    }
    .eia-certificate {
        margin: 0 auto !important;
        page-break-after: avoid !important;
    }
}
</style>

<?php if ($cert) : ?>
    <!-- Action Buttons -->
    <div class="no-print" style="position: fixed; top: 100px; right: 20px; z-index: 1000; display: flex; gap: 1rem;">
        <a href="<?php echo esc_url(add_query_arg(array('eia_certificate_pdf' => '1', 'code' => $certificate_code), home_url('/'))); ?>" style="
            padding: 1rem 1.5rem;
            background: #10B981;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.3)'">
            <i class="fas fa-download" style="margin-right: 0.5rem;"></i>Télécharger PDF
        </a>
        <button onclick="window.print()" style="
            padding: 1rem 1.5rem;
            background: #2D4FB3;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(45, 79, 179, 0.3);
            transition: all 0.2s;
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(45, 79, 179, 0.4)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 12px rgba(45, 79, 179, 0.3)'">
            <i class="fas fa-print" style="margin-right: 0.5rem;"></i>Imprimer
        </button>
    </div>

    <div class="eia-certificate-container">
        <?php
        // Display certificate
        echo $certificates->generate_certificate_html($certificate_code);
        ?>
    </div>

<?php else : ?>
    <!-- Verification Form -->
    <div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 2rem; background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);">
        <div style="max-width: 600px; width: 100%; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); padding: 3rem;">

            <!-- Header -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-certificate" style="font-size: 2.5rem; color: white;"></i>
                </div>
                <h1 style="font-size: 2rem; color: #1f2937; margin: 0 0 0.5rem 0; font-weight: 700;">
                    Vérification de Certificat
                </h1>
                <p style="color: #6b7280; margin: 0; font-size: 1.125rem;">
                    École Internationale des Affaires
                </p>
            </div>

            <!-- Info Box -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
                <p style="margin: 0; color: #15803d; font-size: 0.875rem; line-height: 1.6;">
                    <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
                    Entrez le code de vérification figurant sur le certificat pour confirmer son authenticité
                </p>
            </div>

            <!-- Form -->
            <form method="get" style="margin-bottom: 2rem;">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.875rem;">
                        Code de vérification
                    </label>
                    <input
                        type="text"
                        name="code"
                        placeholder="Ex: EIA-XXXXXXXXXXXX"
                        value="<?php echo esc_attr($certificate_code); ?>"
                        required
                        style="
                            width: 100%;
                            padding: 0.875rem 1rem;
                            border: 2px solid #e5e7eb;
                            border-radius: 8px;
                            font-size: 1rem;
                            font-family: monospace;
                            text-transform: uppercase;
                            transition: all 0.2s;
                            box-sizing: border-box;
                        "
                        onfocus="this.style.borderColor='#10B981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)'"
                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'"
                    >
                </div>

                <button type="submit" style="
                    width: 100%;
                    padding: 1rem;
                    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 1.125rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s;
                    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.3)'">
                    <i class="fas fa-search" style="margin-right: 0.5rem;"></i>Vérifier le certificat
                </button>
            </form>

            <?php if ($certificate_code && !$cert) : ?>
                <!-- Error Message -->
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 1.5rem; text-align: center;">
                    <div style="font-size: 3rem; color: #dc2626; margin-bottom: 1rem;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h3 style="color: #991b1b; margin: 0 0 0.5rem 0; font-size: 1.25rem; font-weight: 600;">
                        Certificat non trouvé
                    </h3>
                    <p style="margin: 0; color: #dc2626; font-size: 0.875rem;">
                        Le code <strong><?php echo esc_html($certificate_code); ?></strong> ne correspond à aucun certificat dans notre système
                    </p>
                </div>
            <?php endif; ?>

            <!-- Features -->
            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
                <h3 style="font-size: 1rem; color: #1f2937; margin: 0 0 1rem 0; font-weight: 600;">
                    Pourquoi vérifier un certificat ?
                </h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 0.5rem 0; color: #6b7280; font-size: 0.875rem;">
                        <i class="fas fa-check-circle" style="color: #10B981; margin-right: 0.5rem;"></i>
                        Confirmer l'authenticité du certificat
                    </li>
                    <li style="padding: 0.5rem 0; color: #6b7280; font-size: 0.875rem;">
                        <i class="fas fa-check-circle" style="color: #10B981; margin-right: 0.5rem;"></i>
                        Vérifier les informations du titulaire
                    </li>
                    <li style="padding: 0.5rem 0; color: #6b7280; font-size: 0.875rem;">
                        <i class="fas fa-check-circle" style="color: #10B981; margin-right: 0.5rem;"></i>
                        Prévenir la fraude et la falsification
                    </li>
                </ul>
            </div>

        </div>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
