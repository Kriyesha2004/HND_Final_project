<?php
session_start();
$userId = $_SESSION['user_id'] ?? 1; // get user ID from session or default to 1
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Care Point - Journaling</title>
  <link rel="stylesheet" href="journaling.css" />
  <style>
    :root { --green:#4ade80; --green-dark:#22c55e; --bg:#0f0f0f; --panel:#1a1a1a; --muted:#bbb; --red:#ef4444; --red-dark:#dc2626; }
    body { background: var(--bg); color: #fff; }

    .btn { display:inline-block; padding:10px 16px; border:none; border-radius:10px; font-weight:700; cursor:pointer; transition:.2s; text-decoration:none; }
    .btn-primary { background: var(--green); color:#000; }
    .btn-primary:hover { background: var(--green-dark); }
    .btn-ghost { background: transparent; border:1px solid rgba(255,255,255,.15); color:#fff; }
    .btn-ghost:hover { background: rgba(255,255,255,.06); }
    .btn-danger { background: var(--red); color:#fff; }
    .btn-danger:hover { background: var(--red-dark); }
    .btn-small { padding: 6px 10px; font-size: 0.85em; }

    .toolbar button { margin-right:6px; }

    .file-row { display:flex; gap:10px; align-items:center; margin:10px 0; }
    .file-input { position: relative; overflow: hidden; display:inline-block; }
    .file-input input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; }
    .file-label { background:#2a2a2a; color:#fff; padding:10px 14px; border-radius:10px; border:1px solid #333; display:inline-block; }
    .file-name { color: var(--muted); font-size:.9em; }

    .panel { background: var(--panel); border:1px solid #2a2a2a; border-radius:12px; padding:12px; }

    .status { margin:10px 0; color: var(--green); font-weight:600; font-size:.95em; }
    .muted { color: var(--muted); font-size:.9em; }

    .sticker-panel { background: #f7f7f7; padding: 10px; border: 1px solid #ccc; display: flex; gap: 8px; overflow-x: auto; margin-bottom: 10px; }
    .sticker-panel img { width: 50px; height: 50px; object-fit: contain; cursor: pointer; transition: transform 0.2s; }
    .sticker-panel img:hover { transform: scale(1.2); }

    .title-row { display:flex; gap:10px; align-items:center; margin:10px 0 14px; }
    .title-row input { flex:1; padding:10px 12px; border-radius:10px; border:1px solid #2a2a2a; background:#111; color:#fff; }

    .layout { display:grid; grid-template-columns: 280px 1fr; gap:16px; }
    .list { background:#131313; border:1px solid #222; border-radius:12px; padding:12px; max-height:520px; overflow:auto; }
    .list h3 { margin: 6px 0 10px 0; color: var(--green); }
    .list-item { padding:10px; border-radius:10px; border:1px solid #222; background:#1a1a1a; margin-bottom:8px; cursor:pointer; position: relative; }
    .list-item:hover { background:#232323; }
    .list-item .title { font-weight:700; margin-bottom: 2px; }
    .list-item .meta { color: var(--muted); font-size:.85em; }
    .list-item .actions { position: absolute; top: 8px; right: 8px; display: none; gap: 6px; }
    .list-item:hover .actions { display: flex; }

    /* Modal styles */
    .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: none; align-items: center; justify-content: center; z-index: 1000; }
    .modal-content { background: var(--panel); border: 1px solid #2a2a2a; border-radius: 12px; padding: 24px; max-width: 400px; width: 90%; }
    .modal-header { margin-bottom: 16px; }
    .modal-header h3 { margin: 0; color: var(--red); }
    .modal-body { margin-bottom: 20px; color: #ccc; line-height: 1.5; }
    .modal-footer { display: flex; gap: 10px; justify-content: flex-end; }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2 class="logo"> Care Point</h2>
    <ul>
      <li><a href="Sidebar.html">🏠 Home</a></li>
      <li><a href="Music.html">🎵 Music</a></li>
      <li class="active"><a href="#">📓 Journaling</a></li>
      <li><a href="cbt.php">🧠 CBT</a></li>
      <li><a href="GroupChat.php">💬Group Chat</a></li>
      <li><a href="Myprofile.php">👤My Profile</a></li>
      <li><a href="Home.html">🚪 Log Out</a></li>
    </ul>
  </div>

  <div class="main-content">
    <header>
      <h1 class="page-title">Journaling</h1>
      <div class="toolbar">
        <button class="btn btn-ghost" onclick="document.execCommand('bold')">B</button>
        <button class="btn btn-ghost" onclick="document.execCommand('italic')">I</button>
        <button class="btn btn-ghost" onclick="document.execCommand('underline')">U</button>
      </div>
    </header>

    <div class="layout">
      <aside class="list">
        <h3>Your Journals</h3>
        <button class="btn btn-primary" style="width:100%; margin-bottom:10px;" onclick="newJournal()">➕ New Journal</button>
        <div id="journalList"></div>
      </aside>

      <section>
        <div class="panel">
          <div class="title-row">
            <label for="journalTitle" class="muted">Title</label>
            <input id="journalTitle" type="text" placeholder="Enter journal title" />
          </div>
          <div id="continueInfo" class="status" style="display:none;"></div>
        </div>

        <h3>Pick a Sticker (optional)</h3>

        <!-- Attach Image (optional). It will upload and insert on Save. -->
        <div id="attachSticker" class="panel">
          <div class="file-row">
            <div class="file-input">
              <span class="file-label">📎 Browse</span>
              <input id="stickerInput" type="file" name="sticker" accept="image/*" />
            </div>
            <span id="selectedFileName" class="file-name">No file selected</span>
          </div>
          <!-- Live preview -->
          <div id="stickerPreviewWrap" style="margin:10px 0; display:none;">
            <p class="muted" style="margin:6px 0;">Preview:</p>
            <img id="stickerPreview" src="" alt="Selected image preview" style="max-width:220px; max-height:220px; border-radius:8px; border:1px solid #333; display:block;" />
          </div>
        </div>

        <!-- Sticker Picker -->
        <div id="stickerPanel" class="sticker-panel"></div>

        <!-- Journal slides -->
        <div class="slides-container panel" id="slidesContainer" style="min-height:220px;">
          <div class="slide active" contenteditable="true" style="min-height:180px; outline:none; position:relative;" placeholder="Start writing your thoughts here... You can also drag & drop images or paste them directly!"></div>
        </div>

        <div style="margin-top:10px; display:flex; gap:10px;">
          <button id="saveBtn" class="btn btn-primary" onclick="saveJournal()">💾 Save</button>
          <button class="btn btn-ghost" onclick="loadLatestJournal()">⤿ Load Latest</button>
        </div>
      </section>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>🗑️ Delete Journal</h3>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete "<strong id="deleteJournalTitle"></strong>"?</p>
        <p style="color: var(--muted); font-size: 0.9em;">This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
        <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
      </div>
    </div>
  </div>

  <script>
    let currentJournalId = null;
    let journalToDelete = null;

    async function fetchJournalList(){
      const userId = <?php echo json_encode($userId); ?>;
      const res = await fetch(`list_journals.php?user_id=${userId}`);
      const data = await res.json();
      const list = document.getElementById('journalList');
      list.innerHTML = '';
      if (data.success && Array.isArray(data.journals)) {
        data.journals.forEach(j => {
          const item = document.createElement('div');
          item.className = 'list-item';
          item.innerHTML = `
            <div class="title">${(j.title||'(untitled)')}</div>
            <div class="meta">Updated: ${j.updated_at}</div>
            <div class="actions">
              <button class="btn btn-danger btn-small" onclick="showDeleteModal(${j.id}, '${(j.title||'(untitled)').replace(/'/g, '&#39;')}')" title="Delete journal">🗑️</button>
            </div>
          `;
          item.addEventListener('click', (e)=> {
            // Don't load journal if clicking on delete button
            if (!e.target.closest('.actions')) {
              loadJournalById(j.id);
            }
          });
          list.appendChild(item);
        });
      } else {
        list.innerHTML = '<div class="muted">No journals yet</div>';
      }
    }

    function showDeleteModal(journalId, journalTitle) {
      journalToDelete = journalId;
      document.getElementById('deleteJournalTitle').textContent = journalTitle;
      document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
      journalToDelete = null;
      document.getElementById('deleteModal').style.display = 'none';
    }

    async function confirmDelete() {
      if (!journalToDelete) return;

      try {
        const res = await fetch('delete_journal.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ journal_id: journalToDelete })
        });

        const data = await res.json();
        if (data.success) {
          // If the deleted journal was currently loaded, clear the editor
          if (currentJournalId === journalToDelete) {
            newJournal();
          }
          
          // Refresh the journal list
          fetchJournalList();
          
          // Show success message briefly
          const saveBtn = document.getElementById('saveBtn');
          const originalText = saveBtn.textContent;
          saveBtn.textContent = '🗑️ Deleted';
          saveBtn.style.background = 'var(--red)';
          setTimeout(() => {
            saveBtn.textContent = originalText;
            saveBtn.style.background = '';
          }, 1500);
          
        } else {
          alert('Failed to delete journal: ' + (data.error || 'Unknown error'));
        }
      } catch (error) {
        alert('Error deleting journal: ' + error.message);
      }

      closeDeleteModal();
    }

    // Close modal when clicking outside of it
    document.getElementById('deleteModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeDeleteModal();
      }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeDeleteModal();
      }
    });

    async function loadJournalById(id){
      const userId = <?php echo json_encode($userId); ?>;
      const res = await fetch(`load_journal.php?user_id=${userId}&journal_id=${id}`);
      const data = await res.json();
      if (data.success && data.journal) {
        const active = document.querySelector('.slide.active');
        active.innerHTML = data.journal.content || '';
        document.getElementById('journalTitle').value = data.journal.title || '';
        currentJournalId = data.journal.id;
        showContinueInfo(data.journal.title || '(untitled)');
      }
    }

    function newJournal(){
      const active = document.querySelector('.slide.active');
      active.innerHTML = '';
      document.getElementById('journalTitle').value = '';
      currentJournalId = null;
      showContinueInfo('');
    }

    function showContinueInfo(title){
      const info = document.getElementById('continueInfo');
      if (title) {
        info.textContent = `Continuing journal: ${title} (ID: ${currentJournalId})`;
        info.style.display = 'block';
      } else {
        info.textContent = '';
        info.style.display = 'none';
      }
    }

    // Enhanced insert sticker function with better positioning
    function insertSticker(url) {
      const activeSlide = document.querySelector(".slide.active");
      if (activeSlide) {
        const img = document.createElement("img");
        img.src = url;
        img.style.maxWidth = "200px";
        img.style.display = "inline-block";
        img.style.margin = "5px";
        img.style.borderRadius = "4px";
        img.style.cursor = "pointer";
        img.draggable = true;

        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
          const range = selection.getRangeAt(0);
          range.insertNode(img);
          range.collapse(false);
        } else {
          activeSlide.appendChild(img);
        }
      }
    }

    // Handle drag and drop images
    function setupDragAndDrop() {
      const slideContainer = document.querySelector('.slide.active');
      
      slideContainer.addEventListener('dragover', (e) => {
        e.preventDefault();
        slideContainer.style.borderColor = 'var(--green)';
      });
      
      slideContainer.addEventListener('dragleave', (e) => {
        slideContainer.style.borderColor = '#333';
      });
      
      slideContainer.addEventListener('drop', (e) => {
        e.preventDefault();
        slideContainer.style.borderColor = '#333';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
          const file = files[0];
          if (file.type.startsWith('image/')) {
            handleImageFile(file);
          }
        }
      });
    }

    // Handle paste images
    function setupPasteImages() {
      const slideContainer = document.querySelector('.slide.active');
      
      slideContainer.addEventListener('paste', (e) => {
        const items = e.clipboardData.items;
        for (let item of items) {
          if (item.type.startsWith('image/')) {
            e.preventDefault();
            const file = item.getAsFile();
            handleImageFile(file);
          }
        }
      });
    }

    // Handle image file (from drag/drop or paste)
    function handleImageFile(file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        insertSticker(e.target.result); // Insert as data URL temporarily
      };
      reader.readAsDataURL(file);
    }

    // Load stickers from DB and display in panel
    async function loadStickers() {
      const res = await fetch("get_stickers.php");
      const stickers = await res.json();
      const panel = document.getElementById("stickerPanel");
      panel.innerHTML = "";
      stickers.forEach((s) => {
        const img = document.createElement("img");
        img.src = s.url;
        img.style.width = "50px";
        img.style.cursor = "pointer";
        img.onclick = () => insertSticker(s.url);
        panel.appendChild(img);
      });
    }

    // Live preview + filename display for selected image before upload
    (function initStickerPreview(){
      const input = document.getElementById('stickerInput');
      const wrap = document.getElementById('stickerPreviewWrap');
      const img = document.getElementById('stickerPreview');
      const nameEl = document.getElementById('selectedFileName');
      let objectUrl = null;

      input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) {
          img.src = '';
          wrap.style.display = 'none';
          nameEl.textContent = 'No file selected';
          if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
          return;
        }
        if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
        objectUrl = URL.createObjectURL(file);
        img.src = objectUrl;
        wrap.style.display = 'block';
        nameEl.textContent = file.name;
      });
    })();

    // Function to convert blob URLs and data URLs to permanent image files
    async function processImagesInContent(content) {
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = content;
      const images = tempDiv.querySelectorAll('img');
      
      for (let img of images) {
        const src = img.src;
        
        // Check if image is a blob URL or data URL (needs to be uploaded)
        if (src.startsWith('blob:') || src.startsWith('data:')) {
          try {
            // Convert to blob
            let blob;
            if (src.startsWith('data:')) {
              // Convert data URL to blob
              const response = await fetch(src);
              blob = await response.blob();
            } else {
              // Get blob from blob URL
              const response = await fetch(src);
              blob = await response.blob();
            }
            
            // Upload the blob as an image
            const formData = new FormData();
            formData.append('sticker', blob, 'journal-image.png');
            
            const uploadRes = await fetch('upload_sticker.php', { 
              method: 'POST', 
              body: formData 
            });
            const uploadData = await uploadRes.json();
            
            if (uploadData.success && uploadData.url) {
              // Replace the temporary URL with the permanent URL
              img.src = uploadData.url;
            }
          } catch (error) {
            console.error('Failed to upload image:', error);
            // Keep the original src if upload fails
          }
        }
      }
      
      return tempDiv.innerHTML;
    }

    // Save current active journal slide content to UserSave_journal.php
    async function saveJournal() {
      const active = document.querySelector(".slide.active");
      if (!active) { alert("No active journal slide to save"); return; }

      const userId = <?php echo json_encode($userId); ?>; // get user ID from PHP session
      const title = document.getElementById('journalTitle').value.trim();

      // Show saving indicator
      const saveBtn = document.getElementById('saveBtn');
      saveBtn.textContent = '💾 Saving...';
      saveBtn.disabled = true;

      try {
        // 1) If a local image file is selected, upload it first and insert into content
        const stickerInput = document.getElementById('stickerInput');
        if (stickerInput && stickerInput.files && stickerInput.files[0]) {
          const formData = new FormData();
          formData.append('sticker', stickerInput.files[0]);
          try {
            const uploadRes = await fetch('upload_sticker.php', { method: 'POST', body: formData });
            const uploadData = await uploadRes.json();
            if (uploadData.success && uploadData.url) {
              insertSticker(uploadData.url);
              // clear chooser and preview
              stickerInput.value = '';
              const wrap = document.getElementById('stickerPreviewWrap');
              const img = document.getElementById('stickerPreview');
              const nameEl = document.getElementById('selectedFileName');
              if (wrap && img && nameEl) {
                img.src = '';
                wrap.style.display = 'none';
                nameEl.textContent = 'No file selected';
              }
              // refresh sticker panel (optional)
              loadStickers();
            }
          } catch (e) {
            console.error('Image upload failed:', e);
          }
        }

        // 2) Process all images in content (convert blob/data URLs to permanent URLs)
        let content = active.innerHTML;
        content = await processImagesInContent(content);

        // 3) Save the journal with processed content
        const payload = { user_id: userId, title: title, content: content };
        if (currentJournalId) payload.id = currentJournalId;

        const res = await fetch('UserSave_journal.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });

        const data = await res.json();
        if (data.success) {
          currentJournalId = data.journal_id;
          showContinueInfo(title || '(untitled)');
          
          saveBtn.textContent = '✅ Saved';
          setTimeout(()=>{ 
            saveBtn.textContent = '💾 Save'; 
            saveBtn.disabled = false;
          }, 1200);
          
          // Update the content in the editor with permanent URLs
          active.innerHTML = content;
          
          fetchJournalList(); // Update the list after saving
        } else {
          saveBtn.textContent = '❌ Failed';
          setTimeout(()=>{ 
            saveBtn.textContent = '💾 Save'; 
            saveBtn.disabled = false;
          }, 2000);
          alert('Save failed: ' + (data.error || 'Unknown error'));
        }
      } catch (error) {
        console.error('Save error:', error);
        saveBtn.textContent = '❌ Error';
        setTimeout(()=>{ 
          saveBtn.textContent = '💾 Save'; 
          saveBtn.disabled = false;
        }, 2000);
        alert('Save failed: ' + error.message);
      }
    }

    // Load the latest journal entry for the logged-in user
    async function loadLatestJournal() {
      const userId = <?php echo json_encode($userId); ?>;
      const res = await fetch(`load_journal.php?user_id=${userId}`);
      const data = await res.json();
      if (data.success && data.journal) {
        const active = document.querySelector(".slide.active");
        if (active) {
          active.innerHTML = data.journal.content || '';
          document.getElementById('journalTitle').value = data.journal.title || '';
          currentJournalId = data.journal.id;
          showContinueInfo(data.journal.title || '(untitled)');
        }
      } else {
        currentJournalId = null;
        showContinueInfo('');
        console.log("No journal found or error:", data.error);
      }
    }

    function newSlide(){
      // Start a fresh journal (doesn't delete existing; just clears current draft)
      const active = document.querySelector('.slide.active');
      if (active) active.innerHTML = '';
      document.getElementById('journalTitle').value = '';
      currentJournalId = null;
      showContinueInfo('');
    }

    // Initialize drag and drop and paste functionality
    setupDragAndDrop();
    setupPasteImages();
    
    loadStickers();
    fetchJournalList();
    loadLatestJournal();
  </script>
</body>
</html>