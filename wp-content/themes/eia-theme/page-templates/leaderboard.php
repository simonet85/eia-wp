<?php
/**
 * Template Name: Tableau de Classement
 *
 * @package EIA_Theme
 */

// Force admin bar visibility
show_admin_bar(true);

get_header();

$gamification = EIA_Gamification::get_instance();
$leaderboard = $gamification->get_leaderboard(50);
$current_user_id = get_current_user_id();
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
</style>

<div style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 4rem 0;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">

        <!-- Header -->
        <div style="text-align: center; color: white; margin-bottom: 3rem;">
            <h1 style="font-size: 3rem; font-weight: 700; margin: 0 0 1rem 0;">
                <i class="fas fa-trophy" style="margin-right: 1rem; color: #F59E0B;"></i>Tableau de Classement
            </h1>
            <p style="font-size: 1.25rem; opacity: 0.9; margin: 0;">
                Top 50 des étudiants les plus actifs
            </p>
        </div>

        <!-- Leaderboard Card -->
        <div style="background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden;">

            <!-- Top 3 Podium -->
            <?php if (count($leaderboard) >= 3) : ?>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3rem 2rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 2rem; align-items: end;">

                        <!-- 2nd Place -->
                        <div style="text-align: center; color: white;">
                            <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #C0C0C0, #E8E8E8); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: #333; border: 4px solid white; box-shadow: 0 8px 20px rgba(0,0,0,0.2);">
                                <?php echo strtoupper(substr($leaderboard[1]->display_name, 0, 1)); ?>
                            </div>
                            <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">
                                <i class="fas fa-medal" style="color: #C0C0C0;"></i>
                            </div>
                            <div style="font-weight: 600; font-size: 1.125rem; margin-bottom: 0.5rem;">
                                <?php echo esc_html($leaderboard[1]->display_name); ?>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 9999px; display: inline-block;">
                                <i class="fas fa-star" style="margin-right: 0.5rem;"></i><?php echo number_format($leaderboard[1]->total_xp); ?> XP
                            </div>
                            <div style="margin-top: 0.5rem; font-size: 0.875rem; opacity: 0.8;">
                                Niveau <?php echo $leaderboard[1]->level; ?>
                            </div>
                        </div>

                        <!-- 1st Place -->
                        <div style="text-align: center; color: white; position: relative; z-index: 1;">
                            <div style="position: absolute; top: -20px; left: 50%; transform: translateX(-50%); font-size: 3rem; animation: bounce 2s infinite;">
                                <i class="fas fa-crown" style="color: #F59E0B;"></i>
                            </div>
                            <div style="width: 100px; height: 100px; margin: 2rem auto 1rem; background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; color: #333; border: 5px solid white; box-shadow: 0 12px 30px rgba(0,0,0,0.3);">
                                <?php echo strtoupper(substr($leaderboard[0]->display_name, 0, 1)); ?>
                            </div>
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">
                                <i class="fas fa-trophy" style="color: #FFD700;"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 1.5rem; margin-bottom: 0.75rem;">
                                <?php echo esc_html($leaderboard[0]->display_name); ?>
                            </div>
                            <div style="background: rgba(255,255,255,0.3); padding: 0.75rem 1.5rem; border-radius: 9999px; display: inline-block; font-size: 1.125rem; font-weight: 600;">
                                <i class="fas fa-star" style="margin-right: 0.5rem;"></i><?php echo number_format($leaderboard[0]->total_xp); ?> XP
                            </div>
                            <div style="margin-top: 0.75rem; font-size: 1rem; opacity: 0.9;">
                                Niveau <?php echo $leaderboard[0]->level; ?>
                            </div>
                        </div>

                        <!-- 3rd Place -->
                        <div style="text-align: center; color: white;">
                            <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #CD7F32, #E39D6C); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: white; border: 4px solid white; box-shadow: 0 8px 20px rgba(0,0,0,0.2);">
                                <?php echo strtoupper(substr($leaderboard[2]->display_name, 0, 1)); ?>
                            </div>
                            <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">
                                <i class="fas fa-medal" style="color: #CD7F32;"></i>
                            </div>
                            <div style="font-weight: 600; font-size: 1.125rem; margin-bottom: 0.5rem;">
                                <?php echo esc_html($leaderboard[2]->display_name); ?>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 9999px; display: inline-block;">
                                <i class="fas fa-star" style="margin-right: 0.5rem;"></i><?php echo number_format($leaderboard[2]->total_xp); ?> XP
                            </div>
                            <div style="margin-top: 0.5rem; font-size: 0.875rem; opacity: 0.8;">
                                Niveau <?php echo $leaderboard[2]->level; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Rest of Leaderboard -->
            <div style="padding: 0;">
                <?php
                $rank = 1;
                foreach ($leaderboard as $entry) :
                    $is_current_user = ($entry->user_id == $current_user_id);
                    $bg_color = $is_current_user ? '#EBF5FF' : 'white';
                    $border_color = $is_current_user ? '#2D4FB3' : '#e5e7eb';
                ?>
                    <div style="display: flex; align-items: center; padding: 1.25rem 2rem; border-bottom: 1px solid #f3f4f6; background: <?php echo $bg_color; ?>; <?php if ($is_current_user) echo 'border-left: 4px solid #2D4FB3;'; ?>">
                        <!-- Rank -->
                        <div style="width: 60px; font-size: 1.5rem; font-weight: 700; color: <?php echo $rank <= 3 ? '#F59E0B' : '#6b7280'; ?>;">
                            #<?php echo $rank; ?>
                        </div>

                        <!-- Avatar -->
                        <div style="width: 50px; height: 50px; background: <?php
                            if ($rank == 1) echo 'linear-gradient(135deg, #FFD700, #FFA500)';
                            elseif ($rank == 2) echo 'linear-gradient(135deg, #C0C0C0, #E8E8E8)';
                            elseif ($rank == 3) echo 'linear-gradient(135deg, #CD7F32, #E39D6C)';
                            else echo 'linear-gradient(135deg, #667eea, #764ba2)';
                        ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; color: white; margin-right: 1.5rem;">
                            <?php echo strtoupper(substr($entry->display_name, 0, 1)); ?>
                        </div>

                        <!-- Name & Info -->
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #1f2937; font-size: 1.125rem; <?php if ($is_current_user) echo 'color: #2D4FB3;'; ?>">
                                <?php echo esc_html($entry->display_name); ?>
                                <?php if ($is_current_user) : ?>
                                    <span style="background: #2D4FB3; color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; margin-left: 0.5rem;">Vous</span>
                                <?php endif; ?>
                            </div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">
                                Niveau <?php echo $entry->level; ?>
                            </div>
                        </div>

                        <!-- XP -->
                        <div style="text-align: right;">
                            <div style="font-weight: 700; font-size: 1.25rem; color: #2D4FB3;">
                                <?php echo number_format($entry->total_xp); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #6b7280;">
                                <i class="fas fa-star" style="color: #F59E0B; margin-right: 0.25rem;"></i>XP
                            </div>
                        </div>
                    </div>
                <?php
                    $rank++;
                endforeach;
                ?>
            </div>
        </div>

        <!-- Back to Dashboard -->
        <div style="text-align: center; margin-top: 3rem;">
            <a href="<?php echo site_url('/mes-cours/'); ?>" style="
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 1rem 2rem;
                background: white;
                color: #667eea;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                transition: all 0.3s;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.3)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>

    </div>
</div>

<style>
@keyframes bounce {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50% { transform: translateX(-50%) translateY(-10px); }
}
</style>

<?php get_footer(); ?>
