<?php

require_once 'AppController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
//require_once __DIR__ . '/../repository/PostRepository.php';
//require_once __DIR__ . '/../repository/CommentRepository.php';

class ModeratorController extends AppController {
    
    private $postRepository;
    private $commentRepository;

    public function __construct() {
    }

    /**
     * Moderator dashboard - content moderation
     */
    public function content(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        // Check if moderator or admin
        if (!in_array($currentUser['role'], ['moderator', 'admin'])) {
            http_response_code(403);
            $this->render('403.html');
            return;
        }

        $this->render('moderator-content.html', ['user' => $currentUser]);
    }
    
    /**
     * Get posts for moderation (AJAX)
     */
    public function getPostsList(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        if (!in_array($currentUser['role'], ['moderator', 'admin'])) {
            $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
            return;
        }

        try {
            // Mock data - replace with real repository calls later
            $posts = $this->getMockPosts();
            
            $this->json([
                'success' => true,
                'posts' => $posts,
                'total' => count($posts)
            ]);
            
        } catch (Exception $e) {
            error_log('Moderator getPostsList error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to fetch posts'], 500);
        }
    }
    
    /**
     * Get comments for moderation 
     */
    public function getCommentsList(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        if (!in_array($currentUser['role'], ['moderator', 'admin'])) {
            $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
            return;
        }

        try {
            // Mock data
            $comments = $this->getMockComments();
            
            $this->json([
                'success' => true,
                'comments' => $comments,
                'total' => count($comments)
            ]);
            
        } catch (Exception $e) {
            error_log('Moderator getCommentsList error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to fetch comments'], 500);
        }
    }
    
    /**
     * Delete post
     */
    public function deletePost(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        if (!in_array($currentUser['role'], ['moderator', 'admin'])) {
            $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
            return;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        
        if (!$postId) {
            $this->json(['success' => false, 'error' => 'Invalid post ID'], 400);
            return;
        }

        try {
            // Mock - replace with real deletion
            // $this->postRepository->delete($postId);
            
            $this->json([
                'success' => true,
                'message' => 'Post deleted successfully'
            ]);
            
        } catch (Exception $e) {
            error_log('Moderator deletePost error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Operation failed'], 500);
        }
    }
    
    /**
     * Delete comment
     */
    public function deleteComment(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        if (!in_array($currentUser['role'], ['moderator', 'admin'])) {
            $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        
        if (!$commentId) {
            $this->json(['success' => false, 'error' => 'Invalid comment ID'], 400);
            return;
        }

        try {
            // Mock - replace with real deletion
            // $this->commentRepository->delete($commentId);
            
            $this->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);
            
        } catch (Exception $e) {
            error_log('Moderator deleteComment error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Operation failed'], 500);
        }
    }
    
    /**
     * Hide post (soft delete)
     */
    public function hidePost(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        if (!in_array($currentUser['role'], ['moderator', 'admin'])) {
            $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
            return;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        
        if (!$postId) {
            $this->json(['success' => false, 'error' => 'Invalid post ID'], 400);
            return;
        }

        try {
            // Mock - replace with real update
            // $this->postRepository->setPublished($postId, false);
            
            $this->json([
                'success' => true,
                'message' => 'Post hidden successfully'
            ]);
            
        } catch (Exception $e) {
            error_log('Moderator hidePost error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Operation failed'], 500);
        }
    }
    
    // ==================== MOCK DATA ====================
    
    private function getMockPosts(): array {
        return [
            [
                'id' => 1,
                'title' => 'Best gluten-free restaurants in Milan',
                'content' => 'I recently discovered some amazing places...',
                'author' => 'Victoria Smith',
                'author_email' => 'victoria1@gmail.com',
                'post_type' => 'tip',
                'likes_count' => 45,
                'comments_count' => 12,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'is_published' => true
            ],
            [
                'id' => 2,
                'title' => 'Cross-contamination concerns',
                'content' => 'Does anyone else worry about cross-contamination when eating out?',
                'author' => 'Tommy S.',
                'author_email' => 'tommy.s@example.com',
                'post_type' => 'question',
                'likes_count' => 23,
                'comments_count' => 34,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                'is_published' => true
            ],
            [
                'id' => 3,
                'title' => 'My celiac diagnosis journey',
                'content' => 'After years of symptoms, I finally got diagnosed...',
                'author' => 'Sarah M.',
                'author_email' => 'sarah.m@example.com',
                'post_type' => 'story',
                'likes_count' => 67,
                'comments_count' => 28,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
                'is_published' => true
            ]
        ];
    }
    
    private function getMockComments(): array {
        return [
            [
                'id' => 1,
                'content' => 'Great tips! I tried the first restaurant and it was amazing.',
                'author' => 'Marco P.',
                'author_email' => 'marco.p@example.com',
                'post_title' => 'Best gluten-free restaurants in Milan',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'is_deleted' => false
            ],
            [
                'id' => 2,
                'content' => 'I always carry my own gluten-free snacks just in case.',
                'author' => 'Alice T.',
                'author_email' => 'alice.t@example.com',
                'post_title' => 'Cross-contamination concerns',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                'is_deleted' => false
            ],
            [
                'id' => 3,
                'content' => 'Thank you for sharing your story. It really resonates with me.',
                'author' => 'Tobby F.',
                'author_email' => 'tobby.f@example.com',
                'post_title' => 'My celiac diagnosis journey',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'is_deleted' => false
            ]
        ];
    }
}