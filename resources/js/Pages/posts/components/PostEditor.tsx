import * as React from 'react';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import TextAlign from '@tiptap/extension-text-align';
import Image from '@tiptap/extension-image';
import CharacterCount from '@tiptap/extension-character-count';
import { AlignCenter, AlignLeft, AlignRight, Bold, Heading2, ImagePlus, Italic, Link2, List, ListOrdered, Quote, Strikethrough, Underline as UnderlineIcon } from 'lucide-react';

interface PostEditorProps {
  value: string;
  onChange: (value: string) => void;
  disabled?: boolean;
  placeholder?: string;
}

interface ToolbarButtonProps {
  label: string;
  title: string;
  active?: boolean;
  disabled?: boolean;
  onClick: () => void;
  icon: React.ReactNode;
}

function ToolbarButton({ label, title, active = false, disabled = false, onClick, icon }: ToolbarButtonProps): React.JSX.Element {
  return (
    <button
      type="button"
      aria-label={label}
      title={title}
      onClick={onClick}
      disabled={disabled}
      className="flex h-9 w-9 items-center justify-center rounded-xl transition-all disabled:cursor-not-allowed disabled:opacity-40"
      style={{
        background: active
          ? 'color-mix(in srgb, var(--accent-primary) 16%, transparent)'
          : 'var(--bg-surface)',
        border: active
          ? '1px solid color-mix(in srgb, var(--accent-primary) 28%, transparent)'
          : '1px solid var(--border-default)',
        color: active ? 'var(--accent-primary)' : 'var(--text-secondary)',
      }}
    >
      {icon}
    </button>
  );
}

export default function PostEditor({
  value,
  onChange,
  disabled = false,
  placeholder = 'Write the body of the post...',
}: PostEditorProps): React.JSX.Element {
  const editor = useEditor({
    immediatelyRender: false,
    editable: !disabled,
    extensions: [
      StarterKit.configure({
        heading: { levels: [2, 3] },
      }),
      Underline,
      Link.configure({
        autolink: true,
        openOnClick: false,
        defaultProtocol: 'https',
      }),
      Placeholder.configure({
        placeholder,
      }),
      TextAlign.configure({
        types: ['heading', 'paragraph'],
      }),
      Image,
      CharacterCount.configure({
        limit: 10000,
      }),
    ],
    content: value,
    onUpdate: ({ editor: currentEditor }) => {
      onChange(currentEditor.getHTML());
    },
  });

  React.useEffect(() => {
    if (!editor) {
      return;
    }

    editor.setEditable(!disabled);
  }, [disabled, editor]);

  React.useEffect(() => {
    if (!editor) {
      return;
    }

    const currentHtml = editor.getHTML();

    if (currentHtml !== value) {
      editor.commands.setContent(value, { emitUpdate: false });
    }
  }, [editor, value]);

  const characterCountStorage = editor?.storage.characterCount as { characters: () => number } | undefined;
  const characterCount = characterCountStorage?.characters() ?? 0;

  function handleSetLink(): void {
    if (!editor) {
      return;
    }

    const previousUrl = editor.getAttributes('link').href as string | undefined;
    const url = window.prompt('Enter the URL', previousUrl ?? 'https://');

    if (url === null) {
      return;
    }

    if (url.trim() === '') {
      editor.chain().focus().unsetLink().run();
      return;
    }

    editor.chain().focus().extendMarkRange('link').setLink({ href: url.trim() }).run();
  }

  function handleInsertImage(): void {
    if (!editor) {
      return;
    }

    const url = window.prompt('Enter the image URL', 'https://');

    if (!url || url.trim() === '') {
      return;
    }

    editor.chain().focus().setImage({ src: url.trim() }).run();
  }

  return (
    <div className="space-y-4">
      <div
        className="flex flex-wrap gap-2 rounded-2xl p-3"
        style={{
          background: 'var(--bg-surface)',
          border: '1px solid var(--border-default)',
        }}
      >
        <ToolbarButton
          label="Bold"
          title="Bold"
          active={editor?.isActive('bold')}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().toggleBold().run()}
          icon={<Bold size={16} />}
        />
        <ToolbarButton
          label="Italic"
          title="Italic"
          active={editor?.isActive('italic')}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().toggleItalic().run()}
          icon={<Italic size={16} />}
        />
        <ToolbarButton
          label="Underline"
          title="Underline"
          active={editor?.isActive('underline')}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().toggleUnderline().run()}
          icon={<UnderlineIcon size={16} />}
        />
        <ToolbarButton
          label="Strike"
          title="Strike"
          active={editor?.isActive('strike')}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().toggleStrike().run()}
          icon={<Strikethrough size={16} />}
        />
        <ToolbarButton
          label="Heading"
          title="Heading"
          active={editor?.isActive('heading', { level: 2 })}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().toggleHeading({ level: 2 }).run()}
          icon={<Heading2 size={16} />}
        />
        <ToolbarButton
          label="Bullet list"
          title="Bullet list"
          active={editor?.isActive('bulletList')}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().toggleBulletList().run()}
          icon={<List size={16} />}
        />
        <ToolbarButton
          label="Ordered list"
          title="Ordered list"
          active={editor?.isActive('orderedList')}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().toggleOrderedList().run()}
          icon={<ListOrdered size={16} />}
        />
        <ToolbarButton
          label="Quote"
          title="Quote"
          active={editor?.isActive('blockquote')}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().toggleBlockquote().run()}
          icon={<Quote size={16} />}
        />
        <ToolbarButton
          label="Align left"
          title="Align left"
          active={editor?.isActive({ textAlign: 'left' })}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().setTextAlign('left').run()}
          icon={<AlignLeft size={16} />}
        />
        <ToolbarButton
          label="Align center"
          title="Align center"
          active={editor?.isActive({ textAlign: 'center' })}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().setTextAlign('center').run()}
          icon={<AlignCenter size={16} />}
        />
        <ToolbarButton
          label="Align right"
          title="Align right"
          active={editor?.isActive({ textAlign: 'right' })}
          disabled={!editor || disabled}
          onClick={() => editor?.chain().focus().setTextAlign('right').run()}
          icon={<AlignRight size={16} />}
        />
        <ToolbarButton
          label="Set link"
          title="Set link"
          active={editor?.isActive('link')}
          disabled={!editor || disabled}
          onClick={handleSetLink}
          icon={<Link2 size={16} />}
        />
        <ToolbarButton
          label="Insert image"
          title="Insert image"
          disabled={!editor || disabled}
          onClick={handleInsertImage}
          icon={<ImagePlus size={16} />}
        />
      </div>

      <div
        className="overflow-hidden rounded-3xl"
        style={{
          background: 'var(--bg-card)',
          border: '1px solid var(--border-default)',
        }}
      >
        <EditorContent
          editor={editor}
          className="min-h-[320px] px-5 py-4 text-sm"
          style={{
            color: 'var(--text-primary)',
            fontFamily: 'var(--font-sans)',
          }}
        />
      </div>

      <div className="flex items-center justify-between gap-3 text-xs" style={{ color: 'var(--text-muted)' }}>
        <span>Use headings, links and alignment controls to structure the article.</span>
        <span>{characterCount} / 10000 characters</span>
      </div>
    </div>
  );
}
