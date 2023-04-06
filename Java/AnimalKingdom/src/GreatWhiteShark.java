
public class GreatWhiteShark extends Fish implements Endangered {

	public static final String GREATWHITE_DESCRIPTION = "Great White Shark";
	public static final BirthType GREATWHITE_BIRTH_TYPE = BirthType.LIVE_BIRTH;
	private boolean isEndangered;
	
	public GreatWhiteShark(int id, String name) {
		super(id, name, GREATWHITE_BIRTH_TYPE);
		this.isEndangered = true;
	}
	
	@Override
	public String getDescription() {
		return super.getDescription() + GREATWHITE_DESCRIPTION
				+ ((isEndangered) ? " (endangered)" : "");
		}

	@Override
	public void displayConservationInformation() {
		System.out.print("Help save the " + GREATWHITE_DESCRIPTION + "!");
		
	}
	
}
