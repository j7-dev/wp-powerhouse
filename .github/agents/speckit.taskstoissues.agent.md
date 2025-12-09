---
description: 根據現有設計文件，將現有 tasks 轉換為可執行、依賴排序的 GitHub issue。
tools: ['github/github-mcp-server/issue_write']
---

**用英文思考，但總是用繁體中文回覆，如果有創建檔案也都使用繁體中文**

## 使用者輸入

```text
$ARGUMENTS
```

在執行前，**必須**考慮使用者輸入（如非空）。

## 大綱

1. 從 repo 根目錄執行 `.specify/scripts/powershell/check-prerequisites.ps1 -Json -RequireTasks -IncludeTasks`，解析 FEATURE_DIR 與 AVAILABLE_DOCS 清單。所有路徑必須為絕對路徑。若參數有單引號如 "I'm Groot"，請用跳脫語法：如 'I'\''m Groot'（或盡量用雙引號）。
2. 從執行結果中，擷取 **tasks** 的路徑。
3. 取得 Git remote，執行：

```bash
git config --get remote.origin.url
```

**只有當 remote 為 GitHub URL 時才可進行下一步**

4. 針對清單中的每個任務，使用 GitHub MCP server 在該遠端倉庫建立新 issue。

**絕對禁止在 remote URL 不符的倉庫建立 issue**
