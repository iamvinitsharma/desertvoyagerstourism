<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationLabel = 'Blog';
    protected static ?string $modelLabel = 'blog post';
    protected static ?string $recordTitleAttribute = 'title';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;
    protected static string|UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->schema([
                Group::make()->columnSpan(2)->schema([
                    Section::make()->schema([
                        TextInput::make('title')->required()->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                                if ($operation === 'create') $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->prefix('/blog/'),
                        Textarea::make('excerpt')->rows(2)->maxLength(400),
                        RichEditor::make('body')->label('Article')->fileAttachmentsDisk('public')->fileAttachmentsDirectory('posts'),
                    ]),
                ]),
                Group::make()->schema([
                    Section::make('Publishing')->schema([
                        Toggle::make('is_published')->label('Published')->default(true),
                        DateTimePicker::make('published_at')->native(false)->default(now()),
                        Select::make('category')->options(['Desert Safari' => 'Desert Safari', 'City Guide' => 'City Guide', 'Tickets' => 'Tickets', 'Travel Tips' => 'Travel Tips'])->searchable(),
                        TextInput::make('read_time')->placeholder('5 min read'),
                    ]),
                    Section::make('Cover image')->schema([
                        FileUpload::make('cover_image')->hiddenLabel()->image()->disk('public')->directory('posts')->imageEditor(),
                    ]),
                    Section::make('Book the experience')->description('Tours/tickets shown beside the article.')->schema([
                        Select::make('related_products')->hiddenLabel()->multiple()->searchable()
                            ->options(fn () => Product::orderBy('title')->pluck('title', 'id')->all()),
                    ]),
                    Section::make('SEO')->collapsed()->schema([
                        TextInput::make('seo_title')->label('SEO title')->maxLength(70),
                        Textarea::make('seo_description')->label('SEO description')->rows(2)->maxLength(320),
                    ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('cover_image')->label('')->disk('public')->imageHeight(48)->extraImgAttributes(['class' => 'rounded-md']),
                TextColumn::make('title')->weight('semibold')->wrap()->searchable()->description(fn ($record) => $record->category),
                TextColumn::make('published_at')->date('d M Y')->sortable(),
                TextColumn::make('read_time')->toggleable(),
                ToggleColumn::make('is_published')->label('Published'),
            ])
            ->recordActions([
                Action::make('site')->label('View')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                    ->url(fn ($record) => rtrim(config('app.frontend_url'), '/') . "/blog/{$record->slug}")->openUrlInNewTab(),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPosts::route('/'), 'create' => CreatePost::route('/create'), 'edit' => EditPost::route('/{record}/edit')];
    }
}
