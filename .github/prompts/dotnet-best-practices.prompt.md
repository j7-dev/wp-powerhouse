---
mode: 'agent'
description: 'Ensure .NET/C# code meets best practices for the solution/project.'
---

# .NET/C# 最佳實踐


你的任務是確保 ${selection} 中的 .NET/C# 程式碼符合本方案/專案的最佳實踐。內容包括：


## 文件與結構

- 為所有公開類別、介面、方法及屬性撰寫完整的 XML 文件註解
- 在 XML 註解中包含參數說明與回傳值說明
- 遵循既定命名空間結構：{Core|Console|App|Service}.{Feature}


## 設計模式與架構

- 依賴注入請使用主建構子語法（如：`public class MyClass(IDependency dependency)`）
- 以泛型基底類別實作 Command Handler 模式（如：`CommandHandler<TOptions>`）
- 介面分離並採用明確命名規則（介面名稱以 'I' 為前綴）
- 複雜物件建立請遵循工廠模式


## 依賴注入與服務

- 建構子注入依賴，並以 ArgumentNullException 進行 null 檢查
- 依服務需求註冊適當生命週期（Singleton、Scoped、Transient）
- 採用 Microsoft.Extensions.DependencyInjection 標準模式
- 實作服務介面以提升可測試性


## 資源管理與在地化

- 使用 ResourceManager 管理本地化訊息與錯誤字串
- 日誌訊息與錯誤訊息資源檔分開管理
- 透過 `_resourceManager.GetString("MessageKey")` 取得資源


## 非同步模式

- 所有 I/O 操作與長時間任務皆使用 async/await
- 非同步方法回傳 Task 或 Task<T>
- 適當時使用 ConfigureAwait(false)
- 正確處理非同步例外狀況


## 測試標準

- 使用 MSTest 框架並搭配 FluentAssertions 進行斷言
- 遵循 AAA 模式（Arrange、Act、Assert）
- 使用 Moq 模擬相依元件
- 測試成功與失敗情境
- 包含 null 參數驗證測試


## 設定與組態

- 使用強型別組態類別並加上資料註解
- 實作驗證屬性（Required、NotEmptyOrWhitespace）
- 設定綁定請用 IConfiguration
- 支援 appsettings.json 組態檔


## Semantic Kernel 與 AI 整合

- AI 操作請用 Microsoft.SemanticKernel
- 正確設定 kernel 並註冊服務
- 處理 AI 模型設定（ChatCompletion、Embedding 等）
- 採用結構化輸出模式以確保 AI 回應可靠


## 錯誤處理與日誌

- 使用 Microsoft.Extensions.Logging 進行結構化日誌
- 日誌需包含有意義的範疇與內容
- 拋出具描述性的特定例外
- 預期失敗情境請用 try-catch 處理


## 效能與安全

- 適用時採用 C# 12+ 新特性及 .NET 8 最佳化
- 正確實作輸入驗證與清理
- 資料庫操作請用參數化查詢
- AI/ML 操作遵循安全程式設計原則


## 程式碼品質

- 確保符合 SOLID 原則
- 透過基底類別與工具類避免程式碼重複
- 命名具意義且反映領域概念
- 方法需聚焦且具內聚性
- 正確實作資源釋放模式