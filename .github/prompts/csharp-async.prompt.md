---
mode: 'agent'
tools: ['changes', 'search/codebase', 'edit/editFiles', 'problems']
description: '取得 C# 非同步程式設計最佳實踐'
---

# C# 非同步程式設計最佳實踐

您的目標是協助我遵循 C# 非同步程式設計的最佳實踐。

## 命名慣例

- 所有非同步方法皆使用 'Async' 結尾
- 方法名稱應與同步版本相對應（例如：`GetDataAsync()` 對應 `GetData()`）

## 回傳型別

- 方法有回傳值時使用 `Task<T>`
- 方法無回傳值時使用 `Task`
- 高效能場景可考慮使用 `ValueTask<T>` 以減少配置
- 除事件處理器外，避免非同步方法回傳 `void`

## 例外處理

- 在 await 表達式周圍使用 try/catch 區塊
- 避免在非同步方法中吞掉例外
- 在函式庫程式碼中適當使用 `ConfigureAwait(false)` 以防止死結
- 非同步 Task 回傳方法中，請用 `Task.FromException()` 傳遞例外而非直接丟出

## 效能

- 多個任務並行執行時使用 `Task.WhenAll()`
- 實作逾時或取第一個完成任務時使用 `Task.WhenAny()`
- 僅傳遞任務結果時避免不必要的 async/await
- 長時間執行的操作請考慮使用取消權杖（CancellationToken）

## 常見陷阱

- 非同步程式碼中切勿使用 `.Wait()`、`.Result` 或 `.GetAwaiter().GetResult()`
- 避免混用阻塞與非同步程式碼
- 除事件處理器外，勿建立 async void 方法
- Task 回傳方法務必使用 await

## 實作模式

- 長時間執行操作可實作 async command 模式
- 處理序列時可使用非同步串流（IAsyncEnumerable<T>）
- 公開 API 可考慮採用基於任務的非同步模式（TAP）

當您檢閱我的 C# 程式碼時，請辨識上述問題並提出符合最佳實踐的改進建議。