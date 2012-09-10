<?php
// Copyright (C) 2010 Combodo SARL
//
//   This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation; version 3 of the License.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

/**
 * Localized data
 *
 * @author      Erwan Taloc <erwan.taloc@combodo.com>
 * @author      Romain Quetiez <romain.quetiez@combodo.com>
 * @author      Denis Flaven <denis.flaven@combodo.com>
 * @author      Tadashi Kaneda <kaneda@rworks.jp>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */


//////////////////////////////////////////////////////////////////////
// Classes in 'gui'
//////////////////////////////////////////////////////////////////////
//

//////////////////////////////////////////////////////////////////////
// Classes in 'application'
//////////////////////////////////////////////////////////////////////
//

//
// Class: AuditCategory
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:AuditCategory' => '監査カテゴリ',
	'Class:AuditCategory+' => '監査全体の内部セクション',
	'Class:AuditCategory/Attribute:name' => 'カテゴリ名',
	'Class:AuditCategory/Attribute:name+' => 'カテゴリの短縮名',
	'Class:AuditCategory/Attribute:description' => '監査カテゴリ説明',
	'Class:AuditCategory/Attribute:description+' => '監査カテゴリの説明',
	'Class:AuditCategory/Attribute:definition_set' => '定義セット',
	'Class:AuditCategory/Attribute:definition_set+' => '監査するべきオブジェクトの集合を定義するOQL式',
	'Class:AuditCategory/Attribute:rules_list' => '監査ルール',
	'Class:AuditCategory/Attribute:rules_list+' => 'このカテゴリの監査ルール',
));

//
// Class: AuditRule
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:AuditRule' => '監査ルール',
	'Class:AuditRule+' => '指定された監査カテゴリをチェックするためのルール',
	'Class:AuditRule/Attribute:name' => 'ルール名',
	'Class:AuditRule/Attribute:name+' => 'ルールの短縮名',
	'Class:AuditRule/Attribute:description' => '監査ルール説明',
	'Class:AuditRule/Attribute:description+' => 'この監査ルールの長い説明',
	'Class:AuditRule/Attribute:query' => '実行するクエリ',
	'Class:AuditRule/Attribute:query+' => '実行するOQL式',
	'Class:AuditRule/Attribute:valid_flag' => '有効なオブジェクト',
	'Class:AuditRule/Attribute:valid_flag+' => 'このルールが有効なオブジェクトを返す場合は真、そうでなければ偽',
	'Class:AuditRule/Attribute:valid_flag/Value:true' => '真',
	'Class:AuditRule/Attribute:valid_flag/Value:true+' => '真',
	'Class:AuditRule/Attribute:valid_flag/Value:false' => '偽',
	'Class:AuditRule/Attribute:valid_flag/Value:false+' => '偽',
	'Class:AuditRule/Attribute:category_id' => 'カテゴリ',
	'Class:AuditRule/Attribute:category_id+' => 'このルールのカテゴリ',
	'Class:AuditRule/Attribute:category_name' => 'カテゴリ',
	'Class:AuditRule/Attribute:category_name+' => 'このルールのカテゴリ名',
));

//
// Class: QueryOQL
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:Query' => 'クエリ',
	'Class:Query+' => 'クエリは動的な方法で定義されるデータセットです。',
	'Class:Query/Attribute:name' => '名前',
	'Class:Query/Attribute:name+' => 'クエリを識別します。',
	'Class:Query/Attribute:description' => '説明',
	'Class:Query/Attribute:description+' => 'クエリの長い説明（目的、使用方法等）',
	'Class:Query/Attribute:fields' => 'フィールド',
	'Class:Query/Attribute:fields+' => 'エクスポートする属性（またはエイリアス属性,alias.attribute）のコンマ区切り(CSV)リスト',

	'Class:QueryOQL' => 'OQL クエリ',
	'Class:QueryOQL+' => ' Object Query Language に基づいたクエリ',
	'Class:QueryOQL/Attribute:oql' => '式',
	'Class:QueryOQL/Attribute:oql+' => 'OQL 式',
));

//////////////////////////////////////////////////////////////////////
// Classes in 'addon/userrights'
//////////////////////////////////////////////////////////////////////
//

//
// Class: User
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:User' => 'ユーザー',
	'Class:User+' => 'ユーザーログイン',
	'Class:User/Attribute:finalclass' => 'アカウントタイプ',
	'Class:User/Attribute:finalclass+' => '',
	'Class:User/Attribute:contactid' => '連絡先(人物)',
	'Class:User/Attribute:contactid+' => 'ビジネスデータから抽出した個人の詳細',
	'Class:User/Attribute:last_name' => 'ラストネーム', 
	'Class:User/Attribute:last_name+' => '対応する連絡先の名前',
	'Class:User/Attribute:first_name' => 'ファーストネーム',
	'Class:User/Attribute:first_name+' => '対応する連絡先のファーストネーム',
	'Class:User/Attribute:email' => 'メールアドレス',
	'Class:User/Attribute:email+' => '対応する連絡先のメールアドレス',
	'Class:User/Attribute:login' => 'ログイン',
	'Class:User/Attribute:login+' => 'ユーザ識別文字列',
	'Class:User/Attribute:language' => '言語',
	'Class:User/Attribute:language+' => 'ユーザ使用言語',
	'Class:User/Attribute:language/Value:EN US' => '英語',
	'Class:User/Attribute:language/Value:EN US+' => '英語(米国)',
	'Class:User/Attribute:language/Value:FR FR' => 'フランス語',
	'Class:User/Attribute:language/Value:FR FR+' => 'フランス語(フランス)',
	'Class:User/Attribute:profile_list' => 'プロフィール',
	'Class:User/Attribute:profile_list+' => '役割、この人物に付与された権限',
	'Class:User/Attribute:allowed_org_list' => '許可された組織',
	'Class:User/Attribute:allowed_org_list+' => 'エンドユーザは以下の組織に属するデータの参照を許可されています。組織が指定されていなければ、制限はありません。',

	'Class:User/Error:LoginMustBeUnique' => 'ログイン名は一意でないといけません。- "%1s" はすでに使われています。',
	'Class:User/Error:AtLeastOneProfileIsNeeded' => '少なくとも1件のプロフィールがこのユーザに指定されなければなりません。',
));

//
// Class: URP_Profiles
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:URP_Profiles' => 'プロフィール',
	'Class:URP_Profiles+' => 'ユーザプロフィール',
	'Class:URP_Profiles/Attribute:name' => '名前',
	'Class:URP_Profiles/Attribute:name+' => 'ラベル',
	'Class:URP_Profiles/Attribute:description' => '説明',
	'Class:URP_Profiles/Attribute:description+' => '1行の説明',
	'Class:URP_Profiles/Attribute:user_list' => 'ユーザー',
	'Class:URP_Profiles/Attribute:user_list+' => 'この役割をもつ人',
));

//
// Class: URP_Dimensions
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:URP_Dimensions' => 'ディメンション',
	'Class:URP_Dimensions+' => 'アプリケーションディメンション(defining silos)',
	'Class:URP_Dimensions/Attribute:name' => '名前',
	'Class:URP_Dimensions/Attribute:name+' => 'ラベル',
	'Class:URP_Dimensions/Attribute:description' => '説明',
	'Class:URP_Dimensions/Attribute:description+' => '1行の説明',
	'Class:URP_Dimensions/Attribute:type' => 'タイプ',
	'Class:URP_Dimensions/Attribute:type+' => 'クラス名、もしくはデータ型(projection unit)',
));

//
// Class: URP_UserProfile
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:URP_UserProfile' => 'ユーザープロフィール',
	'Class:URP_UserProfile+' => 'ユーザープロフィール',
	'Class:URP_UserProfile/Attribute:userid' => 'ユーザー',
	'Class:URP_UserProfile/Attribute:userid+' => 'ユーザアカウント',
	'Class:URP_UserProfile/Attribute:userlogin' => 'ログイン',
	'Class:URP_UserProfile/Attribute:userlogin+' => 'ユーザーのログイン',
	'Class:URP_UserProfile/Attribute:profileid' => 'プロフィール',
	'Class:URP_UserProfile/Attribute:profileid+' => '使用プロフィール',
	'Class:URP_UserProfile/Attribute:profile' => 'プロフィール',
	'Class:URP_UserProfile/Attribute:profile+' => 'プロフィール名',
	'Class:URP_UserProfile/Attribute:reason' => '理由',
	'Class:URP_UserProfile/Attribute:reason+' => 'なぜ、この人物がこの役割を持つかを説明する',
));

//
// Class: URP_UserOrg
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:URP_UserOrg' => 'ユーザー組織',
	'Class:URP_UserOrg+' => '許可された組織',
	'Class:URP_UserOrg/Attribute:userid' => 'ユーザー',
	'Class:URP_UserOrg/Attribute:userid+' => 'ユーザーアカウント',
	'Class:URP_UserOrg/Attribute:userlogin' => 'ログイン',
	'Class:URP_UserOrg/Attribute:userlogin+' => 'ユーザのログイン',
	'Class:URP_UserOrg/Attribute:allowed_org_id' => '組織',
	'Class:URP_UserOrg/Attribute:allowed_org_id+' => '許可された組織',
	'Class:URP_UserOrg/Attribute:allowed_org_name' => '組織',
	'Class:URP_UserOrg/Attribute:allowed_org_name+' => '許可された組織',
	'Class:URP_UserOrg/Attribute:reason' => '理由',
	'Class:URP_UserOrg/Attribute:reason+' => 'なぜこの人物がこの組織に属するデータを参照できるのかを説明する',
));

//
// Class: URP_ProfileProjection
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:URP_ProfileProjection' => 'プロフィールプロジェクション',
	'Class:URP_ProfileProjection+' => 'プロフィールプロジェクション',
	'Class:URP_ProfileProjection/Attribute:dimensionid' => 'ディメンション',
	'Class:URP_ProfileProjection/Attribute:dimensionid+' => 'アプリケーションディメンション',
	'Class:URP_ProfileProjection/Attribute:dimension' => 'ディメンション',
	'Class:URP_ProfileProjection/Attribute:dimension+' => 'アプリケーションディメンション',
	'Class:URP_ProfileProjection/Attribute:profileid' => 'プロフィール',
	'Class:URP_ProfileProjection/Attribute:profileid+' => '使用プロフィール',
	'Class:URP_ProfileProjection/Attribute:profile' => 'プロフィール',
	'Class:URP_ProfileProjection/Attribute:profile+' => 'プロフィール名',
	'Class:URP_ProfileProjection/Attribute:value' => 'Value式',
	'Class:URP_ProfileProjection/Attribute:value+' => '($userを使う)OQL式 | 定数 |  | +属性コード',
	'Class:URP_ProfileProjection/Attribute:attribute' => '属性',
	'Class:URP_ProfileProjection/Attribute:attribute+' => 'ターゲット属性コード (オプション)',
));

//
// Class: URP_ClassProjection
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:URP_ClassProjection' => 'クラスプロジェクション',
	'Class:URP_ClassProjection+' => 'クラスのプロジェクション',
	'Class:URP_ClassProjection/Attribute:dimensionid' => 'ディメンション',
	'Class:URP_ClassProjection/Attribute:dimensionid+' => 'アプリケーションディメンション',
	'Class:URP_ClassProjection/Attribute:dimension' => 'ディメンション',
	'Class:URP_ClassProjection/Attribute:dimension+' => 'アプリケーションディメンション',
	'Class:URP_ClassProjection/Attribute:class' => 'クラス',
	'Class:URP_ClassProjection/Attribute:class+' => 'ターゲットクラス',
	'Class:URP_ClassProjection/Attribute:value' => '値式',
	'Class:URP_ClassProjection/Attribute:value+' => '($this を使った)OQL式 | 定数 |  | +属性コード',
	'Class:URP_ClassProjection/Attribute:attribute' => '属性',
	'Class:URP_ClassProjection/Attribute:attribute+' => 'ターゲット属性コード(オプション)',
));

//
// Class: URP_ActionGrant
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:URP_ActionGrant' => 'アクション権限',
 	'Class:URP_ActionGrant+' => 'クラスに対する権限',
	'Class:URP_ActionGrant/Attribute:profileid' => 'プロフィール',
	'Class:URP_ActionGrant/Attribute:profileid+' => '使用プロフィール',
	'Class:URP_ActionGrant/Attribute:profile' => 'プロフィール',
	'Class:URP_ActionGrant/Attribute:profile+' => '使用プロフィール',
	'Class:URP_ActionGrant/Attribute:class' => 'クラス',
	'Class:URP_ActionGrant/Attribute:class+' => 'ターゲットクラス',
	'Class:URP_ActionGrant/Attribute:permission' => '許可',
	'Class:URP_ActionGrant/Attribute:permission+' => '許可の有無は?',
	'Class:URP_ActionGrant/Attribute:permission/Value:yes' => 'はい',
	'Class:URP_ActionGrant/Attribute:permission/Value:yes+' => 'はい',
	'Class:URP_ActionGrant/Attribute:permission/Value:no' => 'いいえ',
	'Class:URP_ActionGrant/Attribute:permission/Value:no+' => 'いいえ',
	'Class:URP_ActionGrant/Attribute:action' => 'アクション',
	'Class:URP_ActionGrant/Attribute:action+' => '指定されたクラスに実行する操作',
));

//
// Class: URP_StimulusGrant
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:URP_StimulusGrant' => 'シティミュラス権限',
	'Class:URP_StimulusGrant+' => 'オブジェクトのライフサイクル中のシティミュラスにおける権限',
	'Class:URP_StimulusGrant/Attribute:profileid' => 'プロフィール',
	'Class:URP_StimulusGrant/Attribute:profileid+' => '使用プロフィール',
	'Class:URP_StimulusGrant/Attribute:profile' => 'プロフィール',
	'Class:URP_StimulusGrant/Attribute:profile+' => '使用プロフィール',
	'Class:URP_StimulusGrant/Attribute:class' => 'クラス',
	'Class:URP_StimulusGrant/Attribute:class+' => 'ターゲットクラス',
	'Class:URP_StimulusGrant/Attribute:permission' => '権限',
	'Class:URP_StimulusGrant/Attribute:permission+' => '許可されているか、いないか。',
	'Class:URP_StimulusGrant/Attribute:permission/Value:yes' => 'はい',
	'Class:URP_StimulusGrant/Attribute:permission/Value:yes+' => 'はい',
	'Class:URP_StimulusGrant/Attribute:permission/Value:no' => 'いいえ',
	'Class:URP_StimulusGrant/Attribute:permission/Value:no+' => 'いいえ',
	'Class:URP_StimulusGrant/Attribute:stimulus' => 'シティミュラス',
	'Class:URP_StimulusGrant/Attribute:stimulus+' => 'シティミュラスコード',
));

//
// Class: URP_AttributeGrant
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Class:URP_AttributeGrant' => '属性権限',
	'Class:URP_AttributeGrant+' => '属性レベルでの権限',
	'Class:URP_AttributeGrant/Attribute:actiongrantid' => '実行権限',
	'Class:URP_AttributeGrant/Attribute:actiongrantid+' => '実行権限',
	'Class:URP_AttributeGrant/Attribute:attcode' => '属性',
	'Class:URP_AttributeGrant/Attribute:attcode+' => '属性コード',
));

//
// String from the User Interface: menu, messages, buttons, etc...
//

Dict::Add('JA JP', 'Japanese', '日本語', array(
	'Menu:WelcomeMenu' => 'ようこそ',
	'Menu:WelcomeMenu+' => 'ようこそ、iTopへ',
	'Menu:WelcomeMenuPage' => 'ようこそ',
	'Menu:WelcomeMenuPage+' => 'ようこそ、iTopへ',
	'UI:WelcomeMenu:Title' => 'ようこそ、iTopへ',

	'UI:WelcomeMenu:LeftBlock' => '<p>iTopは、オープンソースの、完結したIT運用ポータルです。</p>
<ul>以下を含みます。
<li>ITインベントリを文書化し、管理するための完全なCMDB(構成管理データベース)。</li>
<li>IT環境で発生する出来事を追跡、共有するためのインシデント管理モジュール。</li>
<li>IT環境への変更を計画、追跡するための変更管理モジュール。</li>
<li>インシデントの解決をスピードアップするための既知のエラーデータベース。</li>
<li>すべての計画停止を文書化し、適切な連絡先を通知するために停止モジュール。</li>
<li>ITの概観を素早く得るためのダッシュボード。</li>
</ul>
<p>すべてのモジュールはお互いに独立しており、別個にセットアップが可能です。</p>',

	'UI:WelcomeMenu:RightBlock' => '<p>iTopはサービスプロバイダ志向であり、ITエンジニアが複数の顧客や組織を簡単に管理できるようになります。
<ul>iTopは、機能豊富な下記のビジネスプロセスのセットを提供します。
<li>IT管理の実効性の強化。</li>
<li>IT運用効率化の推進。</li>
<li>顧客満足度の改善と、業績への洞察を経営経営幹部へ提供。</li>
</ul>
</p>
<p>iTopは完全にオープンなので、あなたが今使っているIT管理インフラとの統合が可能です。</p>
<p>
<ul>この新世代IT運用ポータルの採用は、下記のお手伝いをします。
<li>ますます複雑になるIT環境のより良い管理。</li>
<li>あなたのペースでのITILプロセス実装。</li>
<li>ITの中でもっとも重要な財産である「文書化」の管理。</li>
</ul>
</p>',
	'UI:WelcomeMenu:AllOpenRequests' => '要求を開く: %1$d',
	'UI:WelcomeMenu:MyCalls' => '担当中の要求',
	'UI:WelcomeMenu:OpenIncidents' => 'インシデントを開く: %1$d',
	'UI:WelcomeMenu:AllConfigItems' => '構成項目(CI): %1$d',
	'UI:WelcomeMenu:MyIncidents' => '担当中のインシデント',
	'UI:AllOrganizations' => '全ての組織',
	'UI:YourSearch' => '検索',
	'UI:LoggedAsMessage' => '%1$s としてログインする',
	'UI:LoggedAsMessage+Admin' => '%1$s　(管理者)としてログインする',
	'UI:Button:Logoff' => 'ログオフ',
	'UI:Button:GlobalSearch' => '検索',
	'UI:Button:Search' => '　検索　',
	'UI:Button:Query' => ' クエリ',
	'UI:Button:Ok' => 'OK',
	'UI:Button:Cancel' => 'キャンセル',
	'UI:Button:Apply' => '適用する',
	'UI:Button:Back' => ' << 戻る',
	'UI:Button:Restart' => ' |<< リスタート',
	'UI:Button:Next' => ' 次へ >> ',
	'UI:Button:Finish' => ' 終了 ',
	'UI:Button:DoImport' => ' インポート実行! ',
	'UI:Button:Done' => ' 完了 ',
	'UI:Button:SimulateImport' => ' インポートをシュミレート ',
	'UI:Button:Test' => 'テスト実行!',
	'UI:Button:Evaluate' => ' 評価 ',
	'UI:Button:AddObject' => ' 追加...',
	'UI:Button:BrowseObjects' => 'ブラウズ...',
	'UI:Button:Add' => ' 追加 ',
	'UI:Button:AddToList' => ' << 追加 ',
	'UI:Button:RemoveFromList' => '削除 >> ',
	'UI:Button:FilterList' => ' フィルタ... ',
	'UI:Button:Create' => ' 作成 ',
	'UI:Button:Delete' => ' 削除! ',
	'UI:Button:ChangePassword' => ' パスワード変更 ',
	'UI:Button:ResetPassword' => 'パスワードリセット ',
	
	'UI:SearchToggle' => '検索（トグル↓↑)',
	'UI:ClickToCreateNew' => '新規 %1$s を作成',
	'UI:SearchFor_Class' => '%1$s オブジェクトを検索',
	'UI:NoObjectToDisplay' => '表示するオブジェクトはありません。',
	'UI:Error:MandatoryTemplateParameter_object_id' => 'link_attrが指定されている時は、object_idパラメータは必須です。表示テンプレートの定義を確認してください。',
	'UI:Error:MandatoryTemplateParameter_target_attr' => 'link_attrを指定する場合は、target_attrパラメータは必須です。表示テンプレートの定義を確認してください。',
	'UI:Error:MandatoryTemplateParameter_group_by' => 'group_byパラメータは必須です。表示テンプレートの定義を確認してください。',
	'UI:Error:InvalidGroupByFields' => '無効なフィールドリストです。 group by: "%1$s".',
	'UI:Error:UnsupportedStyleOfBlock' => 'エラー：サポートされていないブロックスタイル："%1$s"',
	'UI:Error:IncorrectLinkDefinition_LinkedClass_Class' => '不正なリンク定義: 管理オブジェクトのクラス：%1$s は、クラス %2$s 中の外部キーとして見つかりません。',
	'UI:Error:Object_Class_Id_NotFound' => 'オブジェクト：%1$s:%2$d が見つかりません。',
	'UI:Error:WizardCircularReferenceInDependencies' => 'エラー: フィールド間の依存関係に循環参照があります。データモデルを確認してください。',
	'UI:Error:UploadedFileTooBig' => 'アップロードファイルが大きすぎます(上限は %1$s )。PHPの設定　upload_max_filesizeと、post_max_sizeを確認してください。',
	'UI:Error:UploadedFileTruncated.' => 'アップロードファイルが切り捨てられました!',
	'UI:Error:NoTmpDir' => '一時ディレクトリは定義されていません。',
	'UI:Error:CannotWriteToTmp_Dir' => '一時ファイルをディスクに書き込めません。upload_tmp_dir = "%1$s"',
	'UI:Error:UploadStoppedByExtension_FileName' => 'extensionにより、アップロードを停止しました。(オリジナルのファイル名は"%1$s"です)。',
	'UI:Error:UploadFailedUnknownCause_Code' => 'ファイルのアップロードに失敗しました。原因は不明(エラーコード: "%1$s")です。',

	'UI:Error:1ParametersMissing' => 'エラー: この操作には下記のパラメータを指定する必要があります：%1$s',
	'UI:Error:2ParametersMissing' => 'エラー：この操作には、下記のパラメータを指定する必要があります：%1$s , %2$s',
	'UI:Error:3ParametersMissing' => 'エラー：この操作には、下記のパラメータを指定する必要があります：%1$s, %2$s, %3$s',
	'UI:Error:4ParametersMissing' => 'エラー：この操作には、下記のパラメータを指定する必要があります：%1$s, %2$s, %3$s,%4$s',
	'UI:Error:IncorrectOQLQuery_Message' => 'エラー：誤ったOQLクエリ:%1$s',
	'UI:Error:AnErrorOccuredWhileRunningTheQuery_Message' => 'クエリ:%1$s 実行中にエラーが発生しました。',
	'UI:Error:ObjectAlreadyUpdated' => 'エラー：このオブジェクトはすでに更新済みです。',
	'UI:Error:ObjectCannotBeUpdated' => 'エラー：オブジェクトを更新できません。',
	'UI:Error:ObjectsAlreadyDeleted' => 'エラー：オブジェクトは既に削除されています。',
	'UI:Error:BulkDeleteNotAllowedOn_Class' => '%1$s クラスのオブジェクトに対するバルク削除の実行は許可されていません。',
	'UI:Error:DeleteNotAllowedOn_Class' => '%1$s クラスのオブジェクトの削除は許可されていません。',
	'UI:Error:BulkModifyNotAllowedOn_Class' => '%1$s クラスのオブジェクトに対するバルクアップデートの実行は許可されていません。',
	'UI:Error:ObjectAlreadyCloned' => 'エラー：このオブジェクトはすでに、クローンされています。',
	'UI:Error:ObjectAlreadyCreated' => 'エラー：このオブジェクトは既に作成済みです。',
	'UI:Error:Invalid_Stimulus_On_Object_In_State' => 'エラー：状態"%3$s"のオブジェクト%2$s上の無効なスティミュラス"%1$s".',
	
	
	'UI:GroupBy:Count' => 'カウント',
	'UI:GroupBy:Count+' => '要素数',
	'UI:CountOfObjects' => '%1$d 個のオブジェクトが条件にマッチしました。',
	'UI_CountOfObjectsShort' => '%1$d オブジェクト。',
	'UI:NoObject_Class_ToDisplay' => '表示する %1$s はありません。',
	'UI:History:LastModified_On_By' => '%2$sによる最終更新 %1$s',
	'UI:HistoryTab' => '履歴',
	'UI:NotificationsTab' => '通知',
	'UI:History:BulkImports' => '履歴',
	'UI:History:BulkImports+' => 'CSVインポートのリスト(last first)',
	'UI:History:BulkImportDetails' => '(%2$s により)実行された %1$s へCSVインポートによる変更結果',
	'UI:History:Date' => '日付',
	'UI:History:Date+' => '変更日',
	'UI:History:User' => 'ユーザー',
	'UI:History:User+' => 'この変更を行ったユーザー',
	'UI:History:Changes' => '変更',
	'UI:History:Changes+' => 'このオブジェクトを変更する',
	'UI:History:StatsCreations' => '作成',
	'UI:History:StatsCreations+' => '作成されたオブジェクト数',
	'UI:History:StatsModifs' => '修正',
	'UI:History:StatsModifs+' => '修正されたオブジェクト数',
	'UI:History:StatsDeletes' => '削除',
	'UI:History:StatsDeletes+' => '削除されたオブジェクト数',
	'UI:Loading' => '読み込み...',
	'UI:Menu:Actions' => '実行',
	'UI:Menu:OtherActions' => 'その他の実行',
	'UI:Menu:New' => '新規...',
	'UI:Menu:Add' => '追加...',
	'UI:Menu:Manage' => '管理...',
	'UI:Menu:EMail' => 'Eメール',
	'UI:Menu:CSVExport' => 'CSVエクスポート',
	'UI:Menu:Modify' => '修正...',
	'UI:Menu:Delete' => '削除...',
	'UI:Menu:Manage' => '管理...',
	'UI:Menu:BulkDelete' => '削除...',
	'UI:UndefinedObject' => '未定義',
	'UI:Document:OpenInNewWindow:Download' => '新規ウィンドウで開く: %1$s、 ダウンロード: %2$s',
	'UI:SelectAllToggle+' => 'すべて選択 / すべて非選択',
	'UI:SplitDateTime-Date' => '日付',
	'UI:SplitDateTime-Time' => '時刻',
	'UI:TruncatedResults' => '%2$d中%1$dのオブジェクトを表示',
	'UI:DisplayAll' => 'すべて表示', 
	'UI:CollapseList' => '折りたたむ',
	'UI:CountOfResults' => '%1$d オブジェクト',
	'UI:ChangesLogTitle' => '変更履歴(%1$d)',
	'UI:EmptyChangesLogTitle' => '変更履歴は空です。',
	'UI:SearchFor_Class_Objects' => '%1$s オブジェクトを検索',
	'UI:OQLQueryBuilderTitle' => 'OQLクエリビルダ',
	'UI:OQLQueryTab' => 'OQLクエリ',
	'UI:SimpleSearchTab' => '単純検索',
	'UI:Details+' => '詳細',
	'UI:SearchValue:Any' => '* 任意 *',
	'UI:SearchValue:Mixed' => '* 混成 *',
	'UI:SelectOne' => '-- 選んでください --',
	'UI:Login:Welcome' => 'iTopへようこそ',
	'UI:Login:IncorrectLoginPassword' => 'ログイン/パスワードが正しくありません。再度ログインしてください。',
	'UI:Login:IdentifyYourself' => '続けて作業を行う前に認証を受けてください。', 
	'UI:Login:UserNamePrompt' => 'ユーザー名',
	'UI:Login:PasswordPrompt' => 'パスワード',
	'UI:Login:About' => '',
	'UI:Login:ChangeYourPassword' => 'パスワードを変更してください',
	'UI:Login:OldPasswordPrompt' => '古いパスワード',
	'UI:Login:NewPasswordPrompt' => '新しいパスワード',
	'UI:Login:RetypeNewPasswordPrompt' => '新しいパスワードを再度入力してください。',
	'UI:Login:IncorrectOldPassword' => 'エラー：既存パスワードが正しくありません。',
	'UI:LogOffMenu' => 'ログオフ',
	'UI:LogOff:ThankYou' => 'iTopをご利用いただき、ありがとうございます。',
	'UI:LogOff:ClickHereToLoginAgain' => '再度ログインするにはここをクリックしてください...',
	'UI:ChangePwdMenu' => 'パスワードを変更する...',
	'UI:Login:PasswordChanged' => 'パスワードは変更されました。',
	'UI:AccessRO-All' => 'iTopは参照専用です。',
	'UI:AccessRO-Users' => 'エンドユーザの方はiTopは参照専用です。',
	'UI:Login:RetypePwdDoesNotMatch' => '2度入力された新しいパスワードが一致しません!',
	'UI:Button:Login' => 'iTopへ入る',
	'UI:Login:Error:AccessRestricted' => 'iTopへのアクセスは制限されています。iTop管理者に問い合わせしてください。',
	'UI:Login:Error:AccessAdmin' => '管理者権限をもつユーザにアクセスが制限されています。iTop管理者に問い合わせしてください。',
	'UI:CSVImport:MappingSelectOne' => '-- 選択してください --',
	'UI:CSVImport:MappingNotApplicable' => '--このフィールドを無視する --',
	'UI:CSVImport:NoData' => '空のデータセット..., データを提供してください。',
	'UI:Title:DataPreview' => 'データプレビュー',
	'UI:CSVImport:ErrorOnlyOneColumn' => 'エラー：このデータにはカラムが1つしか含まれていません。適切なセパレータ文字を選択しましたか。',
	'UI:CSVImport:FieldName' => 'フィールド %1$d',
	'UI:CSVImport:DataLine1' => 'データ行 1',
	'UI:CSVImport:DataLine2' => 'データ行 2',
	'UI:CSVImport:idField' => 'ID (主キー)',
	'UI:Title:BulkImport' => 'iTop - バルクインポート',
	'UI:Title:BulkImport+' => 'CSV インポートウィザード',
	'UI:Title:BulkSynchro_nbItem_ofClass_class' => '%2$s クラスの %1$d オブジェクトを同期',
	'UI:CSVImport:ClassesSelectOne' => '--選択してください --',
	'UI:CSVImport:ErrorExtendedAttCode' => '内部エラー： "%2$s" は"%3$s"クラスの外部キーではないので、"%1$s" は誤ったコードです。',
	'UI:CSVImport:ObjectsWillStayUnchanged' => '%1$d オブジェクトは変更されません。',
	'UI:CSVImport:ObjectsWillBeModified' => '%1$d オブジェクトが修正されます。',
	'UI:CSVImport:ObjectsWillBeAdded' => '%1$d オブジェクトが追加されます。',
	'UI:CSVImport:ObjectsWillHaveErrors' => '%1$d オブジェクトにエラーがあります。',
	'UI:CSVImport:ObjectsRemainedUnchanged' => '%1$d オブジェクトは変更されていません。',
	'UI:CSVImport:ObjectsWereModified' => '%1$d オブジェクトが修正されました。',
	'UI:CSVImport:ObjectsWereAdded' => '%1$d オブジェクトが追加されました。',
	'UI:CSVImport:ObjectsHadErrors' => '%1$s オブジェクトにエラーがあります。',
	'UI:Title:CSVImportStep2' => 'ステップ2/5: CSVデータオプション',
	'UI:Title:CSVImportStep3' => 'ステップ3/5: データマッピング',
	'UI:Title:CSVImportStep4' => 'ステップ4/5: インポートシミュレーション',
	'UI:Title:CSVImportStep5' => 'ステップ5/5: インポート完了',
	'UI:CSVImport:LinesNotImported' => 'ロードできなかった行：',
	'UI:CSVImport:LinesNotImported+' => '下記の行はエラーが含まれていたのでインポートされませんでした。',
	'UI:CSVImport:SeparatorComma+' => ', (コンマ)',
	'UI:CSVImport:SeparatorSemicolon+' => '; (セミコロン)',
	'UI:CSVImport:SeparatorTab+' => 'タブ',
	'UI:CSVImport:SeparatorOther' => 'その他:',
	'UI:CSVImport:QualifierDoubleQuote+' => '" (ダブルクォート)',
	'UI:CSVImport:QualifierSimpleQuote+' => '\' (シングルクォート)',
	'UI:CSVImport:QualifierOther' => 'その他：',
	'UI:CSVImport:TreatFirstLineAsHeader' => '1行めをヘッダ(カラム名)として扱う。',
	'UI:CSVImport:Skip_N_LinesAtTheBeginning' => 'ファイル最初の%1$s 行をスキップする',
	'UI:CSVImport:CSVDataPreview' => 'CSVデータプレビュー',
	'UI:CSVImport:SelectFile' => 'インポートするファイルを選択してください:',
	'UI:CSVImport:Tab:LoadFromFile' => 'ファイルからロード',
	'UI:CSVImport:Tab:CopyPaste' => 'データをコピーとペースト',
	'UI:CSVImport:Tab:Templates' => 'テンプレート',
	'UI:CSVImport:PasteData' => 'インポートするデータをペーストしてください:',
	'UI:CSVImport:PickClassForTemplate' => 'ダウンロードするテンプレートを選んでください',
	'UI:CSVImport:SeparatorCharacter' => 'セパレータ文字',
	'UI:CSVImport:TextQualifierCharacter' => 'テキスト修飾子文字',
	'UI:CSVImport:CommentsAndHeader' => 'コメントとヘッダ',
	'UI:CSVImport:SelectClass' => 'インポートするクラスを選択してください:',
	'UI:CSVImport:AdvancedMode' => '拡張モード',
	'UI:CSVImport:AdvancedMode+' => '拡張モードでは、オブジェクトの"id"(主キー)はオブジェクトの更新、リネームに使用可能です。' . 
'しかしながら、"id"カラムは(たとえ存在しても)検索条件として使用可能なだけであり、他の検索条件と組み合わせて利用することはできません。',
	'UI:CSVImport:SelectAClassFirst' => 'マッピングを設定するには、まず最初にクラスを選択してください。',
	'UI:CSVImport:HeaderFields' => 'フィールド',
	'UI:CSVImport:HeaderMappings' => 'マッピング',
	'UI:CSVImport:HeaderSearch' => '検索しますか',
	'UI:CSVImport:AlertIncompleteMapping' => 'すべてのフィールドのマッピングを選択してください。',
	'UI:CSVImport:AlertNoSearchCriteria' => '少なくとも1つ以上の検索条件を選択してください。',
	'UI:CSVImport:Encoding' => '文字エンコーディング',
	'UI:UniversalSearchTitle' => 'iTop - ユニバーサル検索',
	'UI:UniversalSearch:Error' => 'エラー：%1$s',
	'UI:UniversalSearch:LabelSelectTheClass' => '検索するクラスを選択してください。',
	
	'UI:Audit:Title' => 'iTop - CMDB 監査',
	'UI:Audit:InteractiveAudit' => '対話型監査', 
	'UI:Audit:HeaderAuditRule' => '監査ルール',
	'UI:Audit:HeaderNbObjects' => 'オブジェクト数',
	'UI:Audit:HeaderNbErrors' => 'エラー数',
	'UI:Audit:PercentageOk' => '% OK',
	'UI:Audit:ErrorIn_Rule_Reason' => 'ルール %1$s 中のOQLエラー: %2$s.',
	'UI:Audit:ErrorIn_Category_Reason' => 'カテゴリ %1$s 中のOQLエラー: %2$s.',

	'UI:RunQuery:Title' => 'iTop - OQLクエリ評価',
	'UI:RunQuery:QueryExamples' => 'クエリの例',
	'UI:RunQuery:HeaderPurpose' => '目的',
	'UI:RunQuery:HeaderPurpose+' => 'クエリについての説明',
	'UI:RunQuery:HeaderOQLExpression' => 'OQL式',
	'UI:RunQuery:HeaderOQLExpression+' => 'OQL文法によるクエリ',
	'UI:RunQuery:ExpressionToEvaluate' => '評価式',
	'UI:RunQuery:MoreInfo' => 'クエリに関する追加情報',
	'UI:RunQuery:DevelopedQuery' => 'クエリ式の再開発',
	'UI:RunQuery:SerializedFilter' => 'シリアライズされたフィルタ：',
	'UI:RunQuery:Error' => 'クエリ: %1$s 実行時にエラーが発生しました',
	'UI:Query:UrlForExcel' => 'MS-Excel Webクエリに使用するURL',
	'UI:Schema:Title' => 'iTop オブジェクトスキーマ',
	'UI:Schema:CategoryMenuItem' => 'カテゴリ <b>%1$s</b>',
	'UI:Schema:Relationships' => '関係',
	'UI:Schema:AbstractClass' => '抽象クラス：このクラスのインスタンスを作成することはできません。',
	'UI:Schema:NonAbstractClass' => '非抽象クラス：このクラスのインスタンスを作成できます。',
	'UI:Schema:ClassHierarchyTitle' => 'クラス階層',
	'UI:Schema:AllClasses' => '全クラス',
	'UI:Schema:ExternalKey_To' => '%1$s への外部キー',
	'UI:Schema:Columns_Description' => 'カラム： <em>%1$s</em>',
	'UI:Schema:Default_Description' => 'デフォルト： "%1$s"',
	'UI:Schema:NullAllowed' => 'Nullを許容',
	'UI:Schema:NullNotAllowed' => 'Nullを非許容',
	'UI:Schema:Attributes' => '属性',
	'UI:Schema:AttributeCode' => '属性コード',
	'UI:Schema:AttributeCode+' => '属性の内部コード',
	'UI:Schema:Label' => 'ラベル',
	'UI:Schema:Label+' => '属性のラベル',
	'UI:Schema:Type' => '型',
	
	'UI:Schema:Type+' => '属性のデータ型',
	'UI:Schema:Origin' => 'オリジン',
	'UI:Schema:Origin+' => 'この属性が定義されているベースクラス',
	'UI:Schema:Description' => '説明',
	'UI:Schema:Description+' => 'この属性の概要',
	'UI:Schema:AllowedValues' => '可能な値',
	'UI:Schema:AllowedValues+' => '本属性で可能な値の制限',
	'UI:Schema:MoreInfo' => '追加情報',
	'UI:Schema:MoreInfo+' => 'データベースに定義された本フィールドの追加情報',
	'UI:Schema:SearchCriteria' => '検索条件',
	'UI:Schema:FilterCode' => 'フィルタコード',
	'UI:Schema:FilterCode+' => '本検索条件のコード',
	'UI:Schema:FilterDescription' => '説明',
	'UI:Schema:FilterDescription+' => '本検索条件の説明',
	'UI:Schema:AvailOperators' => '利用可能な演算子',
	'UI:Schema:AvailOperators+' => '本検索条件で利用可能な演算子',
	'UI:Schema:ChildClasses' => '子クラス',
	'UI:Schema:ReferencingClasses' => '参照クラス',
	'UI:Schema:RelatedClasses' => '関係するクラス',
	'UI:Schema:LifeCycle' => 'ライフサイクル',
	'UI:Schema:Triggers' => 'トリガー',
	'UI:Schema:Relation_Code_Description' => 'リレーション <em>%1$s</em> (%2$s)',
	'UI:Schema:RelationDown_Description' => '下へ: %1$s',
	'UI:Schema:RelationUp_Description' => '上へ: %1$s', 
	'UI:Schema:RelationPropagates' => '%1$s: %2$d レベルへ伝播、クエリ：%3$s',
	'UI:Schema:RelationDoesNotPropagate' => '%1$s: 伝播しない (%2$d レベル), クエリ: %3$s',
	'UI:Schema:Class_ReferencingClasses_From_By' => '%1$s は%2$s クラスから %3$s フィールドにより参照されている',
	'UI:Schema:Class_IsLinkedTo_Class_Via_ClassAndAttribute' => '%1$s は %3$s::<em>%4$s</em>により%2$s へリンクされています。',
	'UI:Schema:Links:1-n' => 'クラスは%1$sへポイントしています。(1:n リンク)',
	'UI:Schema:Links:n-n' => 'クラスは%1$sへリンクしています。(n:n リンク)',
	'UI:Schema:Links:All' => '関連する全クラスのグラフ表示',
	'UI:Schema:NoLifeCyle' => 'このクラスにはライフサイクルが定義されていません。',
	'UI:Schema:LifeCycleTransitions' => 'トランジション',
	'UI:Schema:LifeCyleAttributeOptions' => '属性オプション',
	'UI:Schema:LifeCycleHiddenAttribute' => '隠し',
	'UI:Schema:LifeCycleReadOnlyAttribute' => '参照のみ',
	'UI:Schema:LifeCycleMandatoryAttribute' => '必須',
	'UI:Schema:LifeCycleAttributeMustChange' => '変更必須',
	'UI:Schema:LifeCycleAttributeMustPrompt' => 'ユーザはこの値を変更するよう、促されます。',
	'UI:Schema:LifeCycleEmptyList' => '空リスト',
	'UI:LinksWidget:Autocomplete+' => '最初の3文字をタイプしてください...',
	'UI:Edit:TestQuery' => 'Test query',
	'UI:Combo:SelectValue' => '--- 値を選んでください ---',
	'UI:Label:SelectedObjects' => '選択されたオブジェクト: ',
	'UI:Label:AvailableObjects' => '利用可能なオブジェクト: ',
	'UI:Link_Class_Attributes' => '%1$s 属性',
	'UI:SelectAllToggle+' => '全てを選択 / 全てを非選択',
	'UI:AddObjectsOf_Class_LinkedWith_Class_Instance' => '%2$s にリンクされた%1$sオブジェクトを追加：%3$s',
	'UI:AddObjectsOf_Class_LinkedWith_Class' => '%1$s オブジェクトを%2$sとのリンクに追加',
	'UI:ManageObjectsOf_Class_LinkedWith_Class_Instance' => '%2$s にリンクされた%1$sオブジェクトの管理: %3$s',
	'UI:AddLinkedObjectsOf_Class' => '%1$s を追加...',
	'UI:RemoveLinkedObjectsOf_Class' => '選択されたオブジェクトを削除',
	'UI:Message:EmptyList:UseAdd' => 'リストは空です。"追加..."ボタンを利用して要素を追加してください。',
	'UI:Message:EmptyList:UseSearchForm' => '上の検索フォームを使って追加するオブジェクトを検索してください。',
	'UI:Wizard:FinalStepTitle' => '最終ステップ：確認',
	'UI:Title:DeletionOf_Object' => '%1$sの削除',
	'UI:Title:BulkDeletionOf_Count_ObjectsOf_Class' => '%2$s クラスの%1$d個のオブジェクトをバルク削除',
	'UI:Delete:NotAllowedToDelete' => 'このオブジェクトの削除は、許可されていません。',
	'UI:Delete:NotAllowedToUpdate_Fields' => '以下のフィールドの更新は、許可されていません。: %1$s',
	'UI:Error:NotEnoughRightsToDelete' => 'カレントユーザは十分な権限を持っていないので、このオブジェクトは削除することができません。',
	'UI:Error:CannotDeleteBecause' => 'このオブジェクトは、削除できません。理由: %1$s',
	'UI:Error:CannotDeleteBecauseOfDepencies' => 'いくつかの手動操作を先に実行する必要があるので、このオブジェクトは削除できません。',
	'UI:Error:CannotDeleteBecauseManualOpNeeded' => 'いくつかの手動操作を先に実行する必要があるので、このオブジェクトは削除できません。',
	'UI:Archive_User_OnBehalfOf_User' => '%2$s の代りに %1$s',
	'UI:Delete:Deleted' => '削除済み',
	'UI:Delete:AutomaticallyDeleted' => '自動的に削除されました。',
	'UI:Delete:AutomaticResetOf_Fields' => 'フィールドの自動リセット: %1$s',
	'UI:Delete:CleaningUpRefencesTo_Object' => '%1$s への参照すべてをクリア',
	'UI:Delete:CleaningUpRefencesTo_Several_ObjectsOf_Class' => '%2$s クラスの　%1$d個のオブジェクトへの参照をすべてクリア',
	'UI:Delete:Done+' => '実行しました...',
	'UI:Delete:_Name_Class_Deleted' => '%1$s - %2$s 削除しました。',
	'UI:Delete:ConfirmDeletionOf_Name' => '%1$s の削除',
	'UI:Delete:ConfirmDeletionOf_Count_ObjectsOf_Class' => '%2$sクラスの%1$dオブジェクトの削除',
	'UI:Delete:CannotDeleteBecause' => '削除できません: %1$s',
	'UI:Delete:ShouldBeDeletedAtomaticallyButNotAllowed' => '自動的に削除されるべきですが、それは許可されていません。',
	'UI:Delete:MustBeDeletedManuallyButNotAllowed' => '手動で削除されるべきですが、このオブジェクトの削除は許可されていません。アプリケーション管理者に問い合わせてください。',
	'UI:Delete:WillBeDeletedAutomatically' => '自動的に削除されます。',
	'UI:Delete:MustBeDeletedManually' => '手動で削除されるべきです。',
	'UI:Delete:CannotUpdateBecause_Issue' => '自動的に更新されるべきですが、しかし: %1$s',
	'UI:Delete:WillAutomaticallyUpdate_Fields' => '自動的に更新されます。(reset: %1$s)',
	'UI:Delete:Count_Objects/LinksReferencing_Object' => '%1$dオブジェクト/リンクは%2$sを参照しています。',
	'UI:Delete:Count_Objects/LinksReferencingTheObjects' => '%1$dオブジェクト/リンクは削除されるオブジェクトを参照しています。',
	'UI:Delete:ReferencesMustBeDeletedToEnsureIntegrity' => 'データベースの整合性を確保するために、いくつかの参照を削除する必要があります。',
	'UI:Delete:Consequence+' => '行われます。',
	'UI:Delete:SorryDeletionNotAllowed' => '申し訳ありませんが、あなたは、このオブジェクトを削除する権限がありません、上記の詳細な説明を参照してください。',
	'UI:Delete:PleaseDoTheManualOperations' => 'このオブジェクトの削除を要求する前に、上記にリストされている手動操作を実行してください。',
	'UI:Delect:Confirm_Object' => '%1$sを削除しようとしています。確認ください。',
	'UI:Delect:Confirm_Count_ObjectsOf_Class' => '以下の%2$sクラスの%1$dオブジェクトを削除しようとしています。確認ください。',
	'UI:WelcomeToITop' => 'iTopへようこそ',
	'UI:DetailsPageTitle' => 'iTop - %1$s - %2$sの詳細',
	'UI:ErrorPageTitle' => 'iTop - エラー',
	'UI:ObjectDoesNotExist' => '申し訳ございません。このオブジェクトは既に存在しません。(あるいは参照する権限がありません。)',
	'UI:SearchResultsPageTitle' => 'iTop - 検索結果',
	'UI:Search:NoSearch' => '検索するものがありません。',
	'UI:FullTextSearchTitle_Text' => '"%1$s"の結果：',
	'UI:Search:Count_ObjectsOf_Class_Found' => '%2$sクラスの%1$dオブジェクトが見つかりました。',
	'UI:Search:NoObjectFound' => 'オブジェクトが見つかりませんでした。',
	'UI:ModificationPageTitle_Object_Class' => 'iTop - %1$s - %2$s 修正',
	'UI:ModificationTitle_Class_Object' => '%1$sの修正： <span class=\"hilite\">%2$s</span>',
	'UI:ClonePageTitle_Object_Class' => 'iTop - クローン%1$s - %2$s 修正',
	'UI:CloneTitle_Class_Object' => '%1$sのクローン：<span class=\"hilite">%2$s</span>',
	'UI:CreationPageTitle_Class' => 'iTop - 新規%1$sを作成',
	'UI:CreationTitle_Class' => '新規%1$sの作成',
	'UI:SelectTheTypeOf_Class_ToCreate' => '作成する%1$sのタイプを選択:',
	'UI:Class_Object_NotUpdated' => '変更は検出されませんでした。%1$s(%2$s)は修正されて<strong>いません</strong>',
	'UI:Class_Object_Updated' => '%1$s (%2$s) は更新されました。',
	'UI:BulkDeletePageTitle' => 'iTop - バルク削除',
	'UI:BulkDeleteTitle' => '削除するオブジェクトを選択してください。',
	'UI:PageTitle:ObjectCreated' => 'iTopオブジェクトが作成されました。',
	'UI:Title:Object_Of_Class_Created' => '%1$s - %2$s が作成されました。',
	'UI:Apply_Stimulus_On_Object_In_State_ToTarget_State' => '状態%3$sにあるオブジェクト：%2$sに、ターゲット状態:%4$sで、%1$sを適用します。',
	'UI:ObjectCouldNotBeWritten' => 'そのオブジェクトへは書き込みできません: %1$s',
	'UI:PageTitle:FatalError' => 'iTop - 致命的なエラー',
	'UI:SystemIntrusion' => 'アクセスが拒否されました。あなたが許可されていない操作を実行しようとしています。',
	'UI:FatalErrorMessage' => '致命的なエラー、ITOPを続行することはできません。',
	'UI:Error_Details' => 'エラー：%1$s',

	'UI:PageTitle:ClassProjections'	=> 'iTop ユーザ管理 - クラスプロジェクション',
	'UI:PageTitle:ProfileProjections' => 'iTop ユーザ管理 - プロフィールプロジェクション',
	'UI:UserManagement:Class' => 'クラス',
	'UI:UserManagement:Class+' => 'オブジェクトのクラス',
	'UI:UserManagement:ProjectedObject' => 'オブジェクト',
	'UI:UserManagement:ProjectedObject+' => 'プロジェクトオブジェクト',
	'UI:UserManagement:AnyObject' => '* 任意 *',
	'UI:UserManagement:User' => 'ユーザ',
	'UI:UserManagement:User+' => 'このプロジェクションに関与しているユーザー',
	'UI:UserManagement:Profile' => 'プロフィール',
	'UI:UserManagement:Profile+' => 'プロジェクションが指定されているプロフィール',
	'UI:UserManagement:Action:Read' => '読み込み',
	'UI:UserManagement:Action:Read+' => 'オブジェクトの読み込み/表示',
	'UI:UserManagement:Action:Modify' => '修正',
	'UI:UserManagement:Action:Modify+' => 'オブジェクトの作成、編集(修正)',
	'UI:UserManagement:Action:Delete' => '削除',
	'UI:UserManagement:Action:Delete+' => 'オブジェクトの削除',
	'UI:UserManagement:Action:BulkRead' => '一括読み出し(エクスポート)',
	'UI:UserManagement:Action:BulkRead+' =>  'オブジェクトのリスト表示、もしくは一括エクスポート',
	'UI:UserManagement:Action:BulkModify' => '一括修正',
	'UI:UserManagement:Action:BulkModify+' => '一括作成/編集(CVSインポート)',
	'UI:UserManagement:Action:BulkDelete' => '一括削除',
	'UI:UserManagement:Action:BulkDelete+' => '複数オブジェクトをまとめて削除',
	'UI:UserManagement:Action:Stimuli' => 'Stimuli',
	'UI:UserManagement:Action:Stimuli+' => '許可されている(複合)アクション',
	'UI:UserManagement:Action' => 'アクション',
	'UI:UserManagement:Action+' => 'ユーザが実行したアクション',
	'UI:UserManagement:TitleActions' => 'アクション',
	'UI:UserManagement:Permission' => 'パーミッション',
	'UI:UserManagement:Permission+' => 'ユーザのパーミッション',
	'UI:UserManagement:Attributes' => '属性',
	'UI:UserManagement:ActionAllowed:Yes' => 'はい',
	'UI:UserManagement:ActionAllowed:No' => 'いいえ',
	'UI:UserManagement:AdminProfile+' => '管理者はデータベース中の全てのオブジェクトに対する全ての読み/書き権限を持っています。',
	'UI:UserManagement:NoLifeCycleApplicable' => 'N/A',
	'UI:UserManagement:NoLifeCycleApplicable+' => 'このクラスにはライフサイクルは定義されていません。',
	'UI:UserManagement:GrantMatrix' => '権限マトリクス',
	'UI:UserManagement:LinkBetween_User_And_Profile' => '%1$s と %2$s間のリンク',
	'UI:UserManagement:LinkBetween_User_And_Org' => '%1$s と %2$s 間のリンク',
	
	'Menu:AdminTools' => '管理ツール',
	'Menu:AdminTools+' => '管理ツール',
	'Menu:AdminTools?' => 'このツールは管理者プロフィールを持つユーザのみアクセスが可能です。',

	'UI:ChangeManagementMenu' => '変更管理',
	'UI:ChangeManagementMenu+' => '変更管理',
	'UI:ChangeManagementMenu:Title' => '変更管理概要',
	'UI-ChangeManagementMenu-ChangesByType' => 'タイプ別変更',
	'UI-ChangeManagementMenu-ChangesByStatus' => '状態別変更',
	'UI-ChangeManagementMenu-ChangesByWorkgroup' => 'ワークグループ別変更',
	'UI-ChangeManagementMenu-ChangesNotYetAssigned' => 'まだ割り当てられていない変更',

	'UI:ConfigurationItemsMenu'=> '設定項目',
	'UI:ConfigurationItemsMenu+'=> 'すべてのデバイス',
	'UI:ConfigurationItemsMenu:Title' => '設定項目概要',
	'UI-ConfigurationItemsMenu-ServersByCriticity' => 'サーバ(by criticity)', // 'Servers by criticity',
	'UI-ConfigurationItemsMenu-PCsByCriticity' => 'PC (by criticity)', // 'PCs by criticity',
	'UI-ConfigurationItemsMenu-NWDevicesByCriticity' => 'ネットワークデバイス (by criticity)', // 'Network devices by criticity',
	'UI-ConfigurationItemsMenu-ApplicationsByCriticity' => 'アプリケーション (by criticity)', // 'Applications by criticity',
	
	'UI:ConfigurationManagementMenu' => '構成管理',
	'UI:ConfigurationManagementMenu+' => '構成管理',
	'UI:ConfigurationManagementMenu:Title' => 'インフラ概要',
	'UI-ConfigurationManagementMenu-InfraByType' => 'タイプ別インフラオブジェクト',
	'UI-ConfigurationManagementMenu-InfraByStatus' => '状態別インフラオブジェクト',

	'UI:ConfigMgmtMenuOverview:Title' => '構成管理ダッシュボード', 
	'UI-ConfigMgmtMenuOverview-FunctionalCIbyStatus' => '状態別構成項目',
	'UI-ConfigMgmtMenuOverview-FunctionalCIByType' => 'タイプ別構成項目',

	'UI:RequestMgmtMenuOverview:Title' => '要求管理ダッシュボード',
	'UI-RequestManagementOverview-RequestByService' => 'サービス別要求',
	'UI-RequestManagementOverview-RequestByPriority' => '優先度別要求',
	'UI-RequestManagementOverview-RequestUnassigned' => 'エージェントへ未割り当て要求',

	'UI:IncidentMgmtMenuOverview:Title' => 'インシデント管理ダッシュボード',
	'UI-IncidentManagementOverview-IncidentByService' => 'サービス別インシデント',
	'UI-IncidentManagementOverview-IncidentByPriority' => '優先度別インシデント',
	'UI-IncidentManagementOverview-IncidentUnassigned' => 'エージェントへ未割り当てインシデント',

	'UI:ChangeMgmtMenuOverview:Title' => '変更管理ダッシュボード',
	'UI-ChangeManagementOverview-ChangeByType' => 'タイプ別変更内容',
	'UI-ChangeManagementOverview-ChangeUnassigned' => 'エージェントへ未割り当て変更内容',
	'UI-ChangeManagementOverview-ChangeWithOutage' => '変更に伴う停止',

	'UI:ServiceMgmtMenuOverview:Title' => 'サービス管理ダッシュボード',
	'UI-ServiceManagementOverview-CustomerContractToRenew' => '30日以内に契約更新が必要な顧客',
	'UI-ServiceManagementOverview-ProviderContractToRenew' => '30日以内に契約更新が必要なプロバイダ',

	'UI:ContactsMenu' => '連絡先',
	'UI:ContactsMenu+' => '連絡先', 
	'UI:ContactsMenu:Title' => '連絡先概要',
	'UI-ContactsMenu-ContactsByLocation' => '場所別連絡先',
	'UI-ContactsMenu-ContactsByType' => 'タイプ別連絡先',
	'UI-ContactsMenu-ContactsByStatus' => '状態別連絡先',

	'Menu:CSVImportMenu' => 'CSV インポート',
	'Menu:CSVImportMenu+' => '一括作成/一括更新',
	
	'Menu:DataModelMenu' => 'データモデル',
	'Menu:DataModelMenu+' => 'データモデル概要',
	
	'Menu:ExportMenu' => 'エクスポート',
	'Menu:ExportMenu+' => '任意のクエリ結果をHTML、CSV、XMLでエクスポートする',
	
	'Menu:NotificationsMenu' => '通知',
	'Menu:NotificationsMenu+' => '通知の設定',
	'UI:NotificationsMenu:Title' => '<span class="hilite">通知</span>の設定',
	'UI:NotificationsMenu:Help' => 'ヘルプ',
	'UI:NotificationsMenu:HelpContent' => '<p>iTopでは、通知はすべてカスタマイズが可能です。通知は<i>トリガーとアクション</i>という二つのオブジェクトがベースになっています。
<p><i><b>トリガー</b></i>は、ある通知がいつ実行されるのか、を定義します。トリガーは3つのタイプに分類され、オブジェクトライフサイクルにおける3つの異なるフェーズに対応します：
<ol>
	<li>"onCreate"トリガーは、指定されたクラスのオブジェクトが作成されたときに実行されます。</li>
	<li>"onStateEnter"トリガーは、指定されたクラスのオブジェクトが(他の状態から)指定された状態に入る前に実行されます。</li>
	<li>"onStateLeave"トリガーは、指定されたクラスのオブジェクトが指定された状態から出る際に実行されます。</li>
</ol>
</p>
<p>
<i><b>アクション</b></i>はトリガーが実行される際の動作を定義します。例えば今、「メールを送信する」という動作で構成されるたった1種類だけのアクションがあるとします。
このようなアクションは、受信者、重要度といったメッセージに付随する他のパラメータと同様、メール送信に利用されるテンプレートも定義します。
</p>
<p>特別なページ: <a href="../setup/email.test.php" target="_blank">email.test.php</p>は、PHPのメール設定をテストしたりトラブルシュートするのに利用可能であす。</p>
<p>実行するには、アクションがトリガーに関連づけられている必要があります。
トリガーに関連づけられると、各々のアクションは順番が与えられ、どの順序でアクションが実行されるかが指定されます。</p>',
	'UI:NotificationsMenu:Triggers' => 'トリガー',
	'UI:NotificationsMenu:AvailableTriggers' => '利用可能トリガー',
	'UI:NotificationsMenu:OnCreate' => 'オブジェクトが作成された時',
	'UI:NotificationsMenu:OnStateEnter' => 'オブジェクトが指定状態に入った時',
	'UI:NotificationsMenu:OnStateLeave' => 'オブジェクトが指定状態から出た時',
	'UI:NotificationsMenu:Actions' => 'アクション',
	'UI:NotificationsMenu:AvailableActions' => '利用可能アクション',
	
	'Menu:AuditCategories' => '監査カテゴリ',
	'Menu:AuditCategories+' => '監査カテゴリ',
	'Menu:Notifications:Title' => '監査カテゴリ',
	
	'Menu:RunQueriesMenu' => 'クエリ実行',
	'Menu:RunQueriesMenu+' => '任意のクエリを実行',
		
	'Menu:QueryMenu' => 'クエリのフレーズブック',
	'Menu:QueryMenu+' => 'クエリのフレーズブック',
	
	'Menu:DataAdministration' => 'データ管理',
	'Menu:DataAdministration+' => 'データ管理',
	
	'Menu:UniversalSearchMenu' => '全検索',
	'Menu:UniversalSearchMenu+' => '何か...検索',
	
	'Menu:ApplicationLogMenu' => 'アプリケーションのログ',
	'Menu:ApplicationLogMenu+' => 'アプリケーションのログ',
	'Menu:ApplicationLogMenu:Title' => 'アプリケーションのログ',

	'Menu:UserManagementMenu' => 'ユーザ管理',
	'Menu:UserManagementMenu+' => 'ユーザ管理',

	'Menu:ProfilesMenu' => 'プロフィール',
	'Menu:ProfilesMenu+' => 'プロフィール',
	'Menu:ProfilesMenu:Title' => 'プロフィール',

	'Menu:UserAccountsMenu' => 'ユーザアカウント',
	'Menu:UserAccountsMenu+' => 'ユーザアカウント',
	'Menu:UserAccountsMenu:Title' => 'ユーザアカウント',

	'UI:iTopVersion:Short' => 'iTopバージョン%1$s',
	'UI:iTopVersion:Long' => 'iTopバージョン%1$s-%2$s ビルド%3$s',
	'UI:PropertiesTab' => 'プロパティ',

	'UI:OpenDocumentInNewWindow_' => '新規ウィンドウでこ文章を開く: %1$s',
	'UI:DownloadDocument_' => 'この文書をダウンロードする: %1$s',
	'UI:Document:NoPreview' => 'このタイプの文書はプレビューできません。',

	'UI:DeadlineMissedBy_duration' => '%1$s によって消去されました。',
	'UI:Deadline_LessThan1Min' => ' < 1分',
	'UI:Deadline_Minutes' => '%1$d 分',
	'UI:Deadline_Hours_Minutes' => '%1$d時間%2$d分',
	'UI:Deadline_Days_Hours_Minutes' => '%1$d日%2$d時間%3$d分',
	'UI:Help' => 'ヘルプ',
	'UI:PasswordConfirm' => '(確認)',
	'UI:BeforeAdding_Class_ObjectsSaveThisObject' => '%1$sオブジェクトをさらに追加する前に、このオブジェクトを保存してください。',
	'UI:DisplayThisMessageAtStartup' => '起動時にこのメッセージを表示する',
	'UI:RelationshipGraph' => 'グラフィカル表示',
	'UI:RelationshipList' => 'リスト',
	'UI:OperationCancelled' => '操作はキャンセルされました',
	'UI:ElementsDisplayed' => 'フィルターリング',

	'Portal:Title' => 'iTopユーザポータル',
	'Portal:Refresh' => 'リフレッシュ',
	'Portal:Back' => '戻る',
	'Portal:WelcomeUserOrg' => 'ようこそ %1$s, %2$sより',
	'Portal:ShowOngoing' => 'オープン中の要求を表示',
	'Portal:ShowClosed' => 'クローズした要求を表示',
	'Portal:CreateNewRequest' => '新規要求を作成',
	'Portal:ChangeMyPassword' => 'パスワードを変更',
	'Portal:Disconnect' => '切断する',
	'Portal:OpenRequests' => '担当のオープン中の要求',
	'Portal:ClosedRequests'  => '担当のクローズした要求',
	'Portal:ResolvedRequests'  => '担当の解決済み要求',
	'Portal:SelectService' => 'カタログからサービスを選択してください：',
	'Portal:PleaseSelectOneService' => 'サービスを1つ選んでください',
	'Portal:SelectSubcategoryFrom_Service' => 'サービス%1$sのサブカテゴリを選んでください:',
	'Portal:PleaseSelectAServiceSubCategory' => 'サブカテゴリを1つ選んでください',
	'Portal:DescriptionOfTheRequest' => 'あなたの要求の説明を記入してください：',
	'Portal:TitleRequestDetailsFor_Request' => '要求%1$sの詳細：',
	'Portal:NoOpenRequest' => 'このカテゴリに要求はありません',
	'Portal:NoClosedRequest' => 'このカテゴリにはクローズした要求はありません。',
	'Portal:Button:ReopenTicket' => 'このチケットを再オープン',
	'Portal:Button:CloseTicket' => 'このチケットをクローズ。',
	'Portal:Button:UpdateRequest' => '要求を更新',
	'Portal:EnterYourCommentsOnTicket' => 'このチケットの解決について、コメントを入力してください。',
	'Portal:ErrorNoContactForThisUser' => 'エラー：現在のユーザは連絡先/人物に関連づけられていません。管理者に問い合わせてください。',
	'Portal:Attachments' => '添付',
	'Portal:AddAttachment' => ' 添付を追加 ',
	'Portal:RemoveAttachment' => ' 添付を削除 ',
	'Portal:Attachment_No_To_Ticket_Name' => '$2$s ($3$s)への添付 #%1$d',
	'Enum:Undefined' => '未定義',
	'UI:DurationForm_Days_Hours_Minutes_Seconds' => '%1$s 日 %2$s 時 %3$s 分 %4$s 秒',
	'UI:ModifyAllPageTitle' => '全てを修正',
	'UI:Modify_N_ObjectsOf_Class' => 'クラス%2$Sの%1$dオブジェクトを修正',
	'UI:Modify_M_ObjectsOf_Class_OutOf_N' => 'クラス%2$sの%3$d中%1$dを修正',
	'UI:Menu:ModifyAll' => '修正...',
	'UI:Button:ModifyAll' => '全て修正',
	'UI:Button:PreviewModifications' => '修正をプレビュー >>',
	'UI:ModifiedObject' => '修正されたオブジェクト',
	'UI:BulkModifyStatus' => '操作',
	'UI:BulkModifyStatus+' => '操作の状態',
	'UI:BulkModifyErrors' => 'エラー (もしあれば)',
	'UI:BulkModifyErrors+' => '修正を出来ないようにしているエラー',	
	'UI:BulkModifyStatusOk' => 'Ok',
	'UI:BulkModifyStatusError' => 'エラー',
	'UI:BulkModifyStatusModified' => '修正',
	'UI:BulkModifyStatusSkipped' => 'スキップ',
	'UI:BulkModify_Count_DistinctValues' => '%1$d 個の個別の値:',
	'UI:BulkModify:Value_Exists_N_Times' => '%1$s, %2$d 回存在',
	'UI:BulkModify:N_MoreValues' => '%1$d 個以上の値...',
	'UI:AttemptingToSetAReadOnlyAttribute_Name' => '読み込み専用フィールド %1$にセットしょうとしています。',
	'UI:FailedToApplyStimuli' => 'アクションは失敗しました。',
	'UI:StimulusModify_N_ObjectsOf_Class' => '%1$s: クラス%3$sの%2$dオブジェクトを修正',
	'UI:CaseLogTypeYourTextHere' => 'テキストを入力ください:',
	'UI:CaseLog:DateFormat' => 'Y-m-d H:i:s',
	'UI:CaseLog:Header_Date_UserName' => '%1$s - %2$s:',
	'UI:CaseLog:InitialValue' => '初期値:',
	'UI:AttemptingToSetASlaveAttribute_Name' => 'フィールド %1$s は、データの同期によってマスターリングされているため書き込み可能ではありません。値は設定されません。',
	'UI:ActionNotAllowed' => 'あなたは、これらのオブジェクトへのこのアクションを許可されていません。',
	'UI:BulkAction:NoObjectSelected' => 'この操作を実行するには、少なくとも1つのオブジェクトを選択してください。',
	'UI:AttemptingToChangeASlaveAttribute_Name' => 'フィールド %1$s はデータの同期によってマスターリングされているため、書き込み可能ではありません。値は変更されません。',
	'UI:Pagination:HeaderSelection' => '全: %1$s オブジェクト (%2$s オブジェクト選択)。',
	'UI:Pagination:HeaderNoSelection' => '全: %1$s オブジェクト。',
	'UI:Pagination:PageSize' => '%1$s オブジェクト／ページ',
	'UI:Pagination:PagesLabel' => 'ページ:',
	'UI:Pagination:All' => '全',
	'UI:HierarchyOf_Class' => '%1$s の階層',
	'UI:Preferences' => 'プリファレンス...',
	'UI:FavoriteOrganizations' => '私の好きな組織',
	'UI:FavoriteOrganizations+' => '迅速なアクセスのためのドロップダウンメニューに表示したい組織は、以下のリストで確認してください。'.
								   'セキュリティ設定ではないことに注意してください。全ての組織のオブジェクトは、表示可能です。ドロップダウンリストで「すべての組織(All Organizations)」を選択することでアクセスすることができます。',
	'UI:NavigateAwayConfirmationMessage' => '全ての変更を破棄します。',
	'UI:Create_Class_InState' => '%1$sを作成、ステート:',
));
?>
