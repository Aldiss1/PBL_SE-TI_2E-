<?php
// Fungsi-fungsi untuk operasi blog
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/anti_injection.php';

// Pastikan session sudah dimulai jika diperlukan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk generate slug
function generateBlogSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    // Replace spaces with hyphens
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    // Remove leading/trailing hyphens
    $text = trim($text, '-');
    return $text;
}

// Fungsi untuk menambahkan blog
function tambahBlog($data) {
    global $koneksi;
    
    $title = isset($data['title']) ? $data['title'] : '';
    $content = isset($data['content']) ? $data['content'] : '';
    $excerpt = isset($data['excerpt']) ? $data['excerpt'] : '';
    $category = isset($data['category']) ? $data['category'] : '';
    $tags = isset($data['tags']) ? $data['tags'] : '';
    $featured_image = isset($data['featured_image']) ? $data['featured_image'] : '';
    $is_published = isset($data['is_published']) ? ($data['is_published'] === true || $data['is_published'] === 'true' || $data['is_published'] === '1') : false;
    
    // Escape untuk PostgreSQL
    $title_escaped = pg_escape_string($koneksi, $title);
    $content_escaped = pg_escape_string($koneksi, $content);
    $excerpt_escaped = pg_escape_string($koneksi, $excerpt);
    $category_escaped = pg_escape_string($koneksi, $category);
    $featured_image_escaped = pg_escape_string($koneksi, $featured_image);
    
    // Generate slug
    $slug = generateBlogSlug($title);
    $slug_escaped = pg_escape_string($koneksi, $slug);
    
    // Cek apakah slug sudah ada
    $check_slug = "SELECT COUNT(*) as count FROM blog WHERE slug = '$slug_escaped'";
    $result_check = pg_query($koneksi, $check_slug);
    if ($result_check) {
        $row_check = pg_fetch_assoc($result_check);
        if ($row_check['count'] > 0) {
            $slug = $slug . '-' . time();
            $slug_escaped = pg_escape_string($koneksi, $slug);
        }
    }
    
    // Get author_id from session
    $author_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
    
    // Convert tags to array format for PostgreSQL
    $tags_array = [];
    if (!empty($tags)) {
        $tags_list = explode(',', $tags);
        foreach ($tags_list as $tag) {
            $tags_array[] = trim($tag);
        }
    }
    $tags_json = json_encode($tags_array);
    $tags_json_escaped = pg_escape_string($koneksi, $tags_json);
    
    // Set published_at
    $published_at_str = $is_published ? "'" . date('Y-m-d H:i:s') . "'" : "NULL";
    $is_published_bool = $is_published ? 'true' : 'false';
    
    // Insert blog
    $query = "INSERT INTO blog (title, slug, content, excerpt, featured_image, author_id, category, tags, is_published, published_at, created_at, updated_at) 
              VALUES ('$title_escaped', '$slug_escaped', '$content_escaped', '$excerpt_escaped', '$featured_image_escaped', $author_id, '$category_escaped', '$tags_json_escaped'::jsonb, $is_published_bool, $published_at_str, NOW(), NOW())";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        return ['success' => true, 'message' => 'Blog berhasil ditambahkan!'];
    } else {
        return ['success' => false, 'message' => 'Gagal menambahkan blog. Error: ' . pg_last_error($koneksi)];
    }
}

// Fungsi untuk mengupdate blog
function updateBlog($id, $data) {
    global $koneksi;
    
    $id = (int)$id;
    $title = isset($data['title']) ? $data['title'] : '';
    $content = isset($data['content']) ? $data['content'] : '';
    $excerpt = isset($data['excerpt']) ? $data['excerpt'] : '';
    $category = isset($data['category']) ? $data['category'] : '';
    $tags = isset($data['tags']) ? $data['tags'] : '';
    $featured_image = isset($data['featured_image']) ? $data['featured_image'] : '';
    $is_published = isset($data['is_published']) ? ($data['is_published'] === true || $data['is_published'] === 'true' || $data['is_published'] === '1') : false;
    
    // Ambil data blog lama untuk cek slug dan published_at
    $query_old = "SELECT slug, is_published FROM blog WHERE id = $id";
    $result_old = pg_query($koneksi, $query_old);
    $blog_old = null;
    if ($result_old && pg_num_rows($result_old) > 0) {
        $blog_old = pg_fetch_assoc($result_old);
    } else {
        return ['success' => false, 'message' => 'Blog tidak ditemukan!'];
    }
    
    // Escape untuk PostgreSQL
    $title_escaped = pg_escape_string($koneksi, $title);
    $content_escaped = pg_escape_string($koneksi, $content);
    $excerpt_escaped = pg_escape_string($koneksi, $excerpt);
    $category_escaped = pg_escape_string($koneksi, $category);
    $featured_image_escaped = pg_escape_string($koneksi, $featured_image);
    
    // Generate slug baru jika title berubah
    $new_slug = generateBlogSlug($title);
    $slug_update = '';
    if ($new_slug !== $blog_old['slug']) {
        $new_slug_escaped = pg_escape_string($koneksi, $new_slug);
        // Cek apakah slug baru sudah ada
        $check_slug = "SELECT COUNT(*) as count FROM blog WHERE slug = '$new_slug_escaped' AND id != $id";
        $result_check = pg_query($koneksi, $check_slug);
        if ($result_check) {
            $row_check = pg_fetch_assoc($result_check);
            if ($row_check['count'] > 0) {
                $new_slug = $new_slug . '-' . time();
                $new_slug_escaped = pg_escape_string($koneksi, $new_slug);
            }
        }
        $slug_update = ", slug = '$new_slug_escaped'";
    }
    
    // Set published_at jika baru dipublish
    $published_at_update = '';
    if ($is_published === true && !$blog_old['is_published']) {
        $published_at = date('Y-m-d H:i:s');
        $published_at_update = ", published_at = '$published_at'";
    }
    
    // Convert tags to array format for PostgreSQL
    $tags_array = [];
    if (!empty($tags)) {
        $tags_list = explode(',', $tags);
        foreach ($tags_list as $tag) {
            $tags_array[] = trim($tag);
        }
    }
    $tags_json = json_encode($tags_array);
    $tags_json_escaped = pg_escape_string($koneksi, $tags_json);
    
    $is_published_bool = $is_published ? 'true' : 'false';
    
    // Update blog
    $query = "UPDATE blog SET 
              title = '$title_escaped',
              content = '$content_escaped',
              excerpt = '$excerpt_escaped',
              featured_image = '$featured_image_escaped',
              category = '$category_escaped',
              tags = '$tags_json_escaped'::jsonb,
              is_published = $is_published_bool
              $slug_update
              $published_at_update,
              updated_at = NOW()
              WHERE id = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        return ['success' => true, 'message' => 'Blog berhasil diperbarui!'];
    } else {
        return ['success' => false, 'message' => 'Gagal memperbarui blog. Error: ' . pg_last_error($koneksi)];
    }
}

// Fungsi untuk menghapus blog
function hapusBlog($id) {
    global $koneksi;
    
    $id = (int)$id;
    
    // Cek apakah blog ada
    $check_query = "SELECT id FROM blog WHERE id = $id";
    $check_result = pg_query($koneksi, $check_query);
    
    if (!$check_result || pg_num_rows($check_result) == 0) {
        return ['success' => false, 'message' => 'Blog tidak ditemukan!'];
    }
    
    // Hapus blog
    $query = "DELETE FROM blog WHERE id = $id";
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        return ['success' => true, 'message' => 'Blog berhasil dihapus!'];
    } else {
        return ['success' => false, 'message' => 'Gagal menghapus blog. Error: ' . pg_last_error($koneksi)];
    }
}

// Fungsi untuk mengambil blog berdasarkan ID
function getBlogById($id) {
    global $koneksi;
    
    $id = (int)$id;
    $query = "SELECT * FROM blog WHERE id = $id";
    $result = pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        $blog = pg_fetch_assoc($result);
        
        // Parse tags from JSON
        if (!empty($blog['tags'])) {
            $tags_array = json_decode($blog['tags'], true);
            if (is_array($tags_array)) {
                $blog['tags_string'] = implode(', ', $tags_array);
            } else {
                $blog['tags_string'] = '';
            }
        } else {
            $blog['tags_string'] = '';
        }
        
        return $blog;
    }
    
    return null;
}

// Fungsi untuk mengambil semua blog
function getAllBlogs() {
    global $koneksi;
    
    $blogs = [];
    $query = "SELECT b.*, u.username as author_name 
              FROM blog b 
              LEFT JOIN users u ON b.author_id = u.id 
              ORDER BY b.created_at DESC";
    $result = pg_query($koneksi, $query);
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $blogs[] = $row;
        }
    }
    
    return $blogs;
}
?>

