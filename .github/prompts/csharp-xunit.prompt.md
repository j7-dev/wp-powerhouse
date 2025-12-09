---
mode: 'agent'
tools: ['changes', 'search/codebase', 'edit/editFiles', 'problems', 'search']
description: 'Get best practices for XUnit unit testing, including data-driven tests'
---


# XUnit 最佳實踐


你的目標是協助我使用 XUnit 撰寫有效的單元測試，涵蓋標準測試與資料驅動測試方法。


## 專案設定

- 使用獨立的測試專案，命名規則為 `[ProjectName].Tests`
- 引用 Microsoft.NET.Test.Sdk、xunit 及 xunit.runner.visualstudio 套件
- 測試類別名稱需與被測試類別相對應（例如 `CalculatorTests` 對應 `Calculator`）
- 使用 .NET SDK 測試指令：執行測試請用 `dotnet test`


## 測試結構

- 不需要測試類別屬性（不同於 MSTest/NUnit）
- 簡單測試請用 `[Fact]` 屬性
- 遵循 Arrange-Act-Assert（AAA）模式
- 測試命名規則：`方法名稱_情境_預期行為`
- 使用建構子進行初始化，`IDisposable.Dispose()` 進行清理
- 使用 `IClassFixture<T>` 於同一類別內共享測試情境
- 使用 `ICollectionFixture<T>` 於多個測試類別間共享測試情境


## 標準測試

- 測試應聚焦於單一行為
- 避免在同一測試方法中測試多個行為
- 使用明確的斷言以表達意圖
- 只包含驗證該測試案例所需的斷言
- 測試需獨立且具冪等性（可任意順序執行）
- 避免測試間相互依賴


## 資料驅動測試

- 使用 `[Theory]` 結合資料來源屬性
- 用 `[InlineData]` 傳遞內嵌測試資料
- 用 `[MemberData]` 傳遞方法型測試資料
- 用 `[ClassData]` 傳遞類別型測試資料
- 實作 `DataAttribute` 以建立自訂資料屬性
- 資料驅動測試請使用具意義的參數名稱


## 斷言

- 使用 `Assert.Equal` 驗證值相等
- 使用 `Assert.Same` 驗證參考相等
- 使用 `Assert.True`/`Assert.False` 驗證布林條件
- 使用 `Assert.Contains`/`Assert.DoesNotContain` 驗證集合內容
- 使用 `Assert.Matches`/`Assert.DoesNotMatch` 驗證正則表達式
- 使用 `Assert.Throws<T>` 或 `await Assert.ThrowsAsync<T>` 驗證例外狀況
- 可搭配 fluent assertions 套件提升斷言可讀性


## 模擬與隔離

- 建議搭配 Moq 或 NSubstitute 使用 XUnit
- 模擬相依元件以隔離被測單元
- 透過介面方便進行模擬
- 複雜測試情境可考慮使用 DI 容器


## 測試組織

- 依功能或元件分組測試
- 用 `[Trait("Category", "CategoryName")]` 進行分類
- 用 collection fixture 管理有共用相依的測試
- 可用輸出輔助工具（`ITestOutputHelper`）進行測試診斷
- 可於 fact/theory 屬性中以 `Skip = "原因"` 條件性略過測試